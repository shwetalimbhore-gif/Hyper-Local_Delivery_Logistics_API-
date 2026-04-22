<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\ParcelStatus;
use App\Models\Rider;
use App\Models\Hub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ParcelController extends Controller
{
    /**
     * Get all parcels (admin: all, rider: assigned to them)
     * GET /api/parcels
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Parcel::with(['status', 'assignedRider.user', 'sourceHub']);

        if ($user->isRider()) {
            $query->where('assigned_rider_id', $user->rider->id);
        }

        $parcels = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $parcels
        ]);
    }

    /**
     * Get single parcel details
     * GET /api/parcels/{id}
     */
    public function show($id, Request $request)
    {
        $user = $request->user();
        $parcel = Parcel::with(['status', 'assignedRider.user', 'sourceHub', 'statusHistories.updater'])
            ->find($id);

        if (!$parcel) {
            return response()->json([
                'success' => false,
                'message' => 'Parcel not found'
            ], 404);
        }

        // Check authorization
        if ($user->isRider() && $parcel->assigned_rider_id !== $user->rider->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $parcel
        ]);
    }

    /**
     * Create new parcel (admin only)
     * POST /api/parcels
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:20',
            'sender_address' => 'required|string',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'receiver_address' => 'required|string',
            'parcel_name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0.1',
            'size' => 'required|numeric|min:0.1',
            'delivery_charge' => 'required|numeric|min:0',
            'source_hub_id' => 'required|exists:hubs,id',
            'assigned_rider_id' => 'nullable|exists:riders,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $data = $validator->validated();
            $data['tracking_number'] = Parcel::generateTrackingNumber();
            $data['created_by'] = $user->id;

            // Auto-assign if no rider selected
            if (empty($data['assigned_rider_id'])) {
                $bestRider = Rider::findBestForParcel($data['weight'], $data['size'], $data['source_hub_id']);
                if ($bestRider) {
                    $data['assigned_rider_id'] = $bestRider->id;
                    $data['status_id'] = ParcelStatus::where('slug', 'assigned')->first()->id;
                    $data['assigned_at'] = now();
                    $bestRider->updateStatus('busy');
                } else {
                    $data['status_id'] = ParcelStatus::where('slug', 'pending')->first()->id;
                }
            } else {
                $data['status_id'] = ParcelStatus::where('slug', 'assigned')->first()->id;
                $data['assigned_at'] = now();
                $rider = Rider::find($data['assigned_rider_id']);
                if ($rider) {
                    $rider->updateStatus('busy');
                }
            }

            $parcel = Parcel::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Parcel created successfully',
                'data' => $parcel
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create parcel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update parcel (admin only)
     * PUT /api/parcels/{id}
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $parcel = Parcel::find($id);

        if (!$parcel) {
            return response()->json([
                'success' => false,
                'message' => 'Parcel not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'sender_name' => 'sometimes|string|max:255',
            'receiver_name' => 'sometimes|string|max:255',
            'weight' => 'sometimes|numeric|min:0.1',
            'delivery_charge' => 'sometimes|numeric|min:0',
            'assigned_rider_id' => 'sometimes|exists:riders,id',
            'status_id' => 'sometimes|exists:parcel_statuses,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $parcel->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Parcel updated successfully',
            'data' => $parcel
        ]);
    }

    /**
     * Update parcel status (Admin or Rider)
     * PATCH /api/parcels/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();
        $parcel = Parcel::find($id);

        if (!$parcel) {
            return response()->json([
                'success' => false,
                'message' => 'Parcel not found'
            ], 404);
        }

        // Check authorization: Admin OR assigned rider can update status
        $isAuthorized = false;

        if ($user->isAdmin()) {
            $isAuthorized = true;  // Admin can update any parcel
        } elseif ($user->isRider() && $parcel->assigned_rider_id === $user->rider->id) {
            $isAuthorized = true;  // Rider can only update their assigned parcels
        }

        if (!$isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You are not allowed to update this parcel status.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status_id' => 'required|exists:parcel_statuses,id',
            'failure_reason' => 'required_if:status_id,6|nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $newStatus = ParcelStatus::find($request->status_id);

        // For riders, check if status transition is allowed
        if ($user->isRider() && !$parcel->canUpdateStatus($request->status_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $oldStatusId = $parcel->status_id;
            $parcel->status_id = $newStatus->id;

            // Update timestamp based on status
            switch ($newStatus->slug) {
                case 'picked-up':
                    $parcel->picked_up_at = now();
                    break;
                case 'out-for-delivery':
                    $parcel->out_for_delivery_at = now();
                    break;
                case 'delivered':
                    $parcel->delivered_at = now();
                    break;
                case 'failed-delivery':
                    $parcel->failed_delivery_at = now();
                    $parcel->delivery_attempts++;
                    if ($request->failure_reason) {
                        $parcel->failure_reason = $request->failure_reason;
                    }
                    break;
                case 'returned-to-hub':
                    $parcel->returned_at = now();
                    break;
            }

            $parcel->save();

            // Create history record
            \App\Models\ParcelStatusHistory::create([
                'parcel_id' => $parcel->id,
                'status_id' => $newStatus->id,
                'from_status_id' => $oldStatusId,
                'notes' => $request->notes ?? $request->failure_reason,
                'updated_by' => $user->id,
            ]);

            // Update rider stats if delivered (only if updated by rider or admin)
            if ($newStatus->slug === 'delivered' && $parcel->assigned_rider_id) {
                $rider = Rider::find($parcel->assigned_rider_id);
                if ($rider) {
                    $rider->earnings += $parcel->delivery_charge * 0.7;
                    $rider->successful_deliveries++;
                    $rider->total_deliveries++;
                    $rider->status = 'available';
                    $rider->save();
                }
            } elseif ($newStatus->slug === 'failed-delivery' && $parcel->assigned_rider_id) {
                $rider = Rider::find($parcel->assigned_rider_id);
                if ($rider) {
                    $rider->failed_deliveries++;
                    $rider->total_deliveries++;
                    $rider->save();
                }
            } elseif ($newStatus->slug === 'returned-to-hub' && $parcel->assigned_rider_id) {
                $rider = Rider::find($parcel->assigned_rider_id);
                if ($rider) {
                    $rider->status = 'available';
                    $rider->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully to: ' . $newStatus->display_name,
                'data' => $parcel->load('status')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Delete parcel (soft delete) - admin only
     * DELETE /api/parcels/{id}
     */
    public function destroy($id, Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $parcel = Parcel::find($id);

        if (!$parcel) {
            return response()->json([
                'success' => false,
                'message' => 'Parcel not found'
            ], 404);
        }

        $parcel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Parcel deleted successfully'
        ]);
    }

    /**
     * Track parcel by tracking number (public)
     * POST /api/track
     */
    public function track(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tracking_number' => 'required|string|exists:parcels,tracking_number',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid tracking number'
            ], 422);
        }

        $parcel = Parcel::with(['status', 'assignedRider.user', 'sourceHub'])
            ->where('tracking_number', $request->tracking_number)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tracking_number' => $parcel->tracking_number,
                'status' => [
                    'name' => $parcel->status->display_name,
                    'slug' => $parcel->status->slug,
                    'color' => $parcel->status->color_code,
                ],
                'sender' => [
                    'name' => $parcel->sender_name,
                    'phone' => $parcel->sender_phone,
                    'address' => $parcel->sender_address,
                ],
                'receiver' => [
                    'name' => $parcel->receiver_name,
                    'phone' => $parcel->receiver_phone,
                    'address' => $parcel->receiver_address,
                ],
                'parcel_details' => [
                    'name' => $parcel->parcel_name,
                    'weight' => $parcel->weight,
                    'type' => $parcel->parcel_type,
                    'delivery_charge' => $parcel->delivery_charge,
                ],
                'rider' => $parcel->assignedRider ? [
                    'name' => $parcel->assignedRider->user->name,
                    'vehicle' => $parcel->assignedRider->vehicle_type,
                ] : null,
                'last_updated' => $parcel->updated_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
