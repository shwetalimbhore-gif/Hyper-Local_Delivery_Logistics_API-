<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\Notification;
use App\Models\Hub;
use App\Models\Rider;
use App\Models\ParcelStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Admin\ParcelStoreRequest;
use App\Http\Requests\Admin\ParcelUpdateRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ParcelStatusHistory;


class ParcelController extends Controller
{
     /**
     * Display parcels index page
     */
    public function index()
    {
        return view('admin.parcels.index');
    }

   /**
     * Get parcels data for DataTable via AJAX
     */
    public function getData(Request $request)
    {
        $parcels = Parcel::with(['status', 'assignedRider.user', 'sourceHub'])
            ->select(
                'parcels.id',
                'parcels.tracking_number',
                'parcels.sender_name',
                'parcels.receiver_name',
                'parcels.weight',
                'parcels.created_at',
                'parcels.status_id',
                'parcels.assigned_rider_id'
            );

        return DataTables::eloquent($parcels)
            ->editColumn('id', fn($parcel) => $parcel->id)
            ->editColumn('tracking_number', fn($parcel) => $parcel->tracking_number)
            ->editColumn('sender_name', fn($parcel) => $parcel->sender_name)
            ->editColumn('receiver_name', fn($parcel) => $parcel->receiver_name)
            ->editColumn('weight', fn($parcel) => $parcel->weight . ' kg')
            ->editColumn('created_at', fn($parcel) => $parcel->created_at->format('d M Y'))
            ->addColumn('status_html', function($parcel) {
                $color = $parcel->status->color_code ?? '#6c757d';
                return '<span class="badge" style="background-color: ' . $color . '; color: white; padding: 6px 12px;">'
                    . ($parcel->status->display_name ?? 'Unknown') . '</span>';
            })
            ->addColumn('rider_name', function($parcel) {
                return $parcel->assignedRider->user->name ?? '<span class="text-muted">Unassigned</span>';
            })
            ->addColumn('actions', function($parcel) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="' . route('admin.parcels.show', $parcel->id) . '" class="btn btn-info btn-sm" title="View">
                            <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                        </a>
                        <a href="' . route('admin.parcels.edit', $parcel->id) . '" class="btn btn-primary btn-sm" title="Edit">
                            <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                        </a>
                        <button type="button" class="btn btn-warning btn-sm soft-delete-btn"
                                data-id="' . $parcel->id . '"
                                data-tracking="' . $parcel->tracking_number . '"
                                title="Move to Trash">
                            <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status_html', 'rider_name', 'actions'])
            ->toJson();
    }


    /**
     * Display trash page
     */
    public function trash()
    {
        return view('admin.parcels.trash');
    }

    /**
     * Get trashed parcels data for DataTable via AJAX
     */
    public function getTrashData(Request $request)
    {
        try {
            $parcels = Parcel::onlyTrashed()
                ->select(
                    'id',
                    'tracking_number',
                    'sender_name',
                    'receiver_name',
                    'deleted_at'
                );

            return DataTables::eloquent($parcels)
                ->addColumn('checkbox', function($parcel) {
                    return '<input type="checkbox" class="parcel-checkbox" value="' . $parcel->id . '">';
                })
                ->addColumn('actions', function($parcel) {
                    return '
                        <button class="btn btn-sm btn-success" onclick="showRestoreModal(' . $parcel->id . ', \'' . addslashes($parcel->tracking_number) . '\')">
                            <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                            Restore
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="showForceDeleteModal(' . $parcel->id . ', \'' . addslashes($parcel->tracking_number) . '\')">
                            <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                            Delete Forever
                        </button>
                    ';
                })
                ->rawColumns(['checkbox', 'actions'])
                ->make(true);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function create()
    {
        $hubs = Hub::where('is_active', true)->get();
        return view('admin.parcels.create', compact('hubs'));
    }

    /**
     * Store a newly created parcel (Using FormRequest)
     */
    public function store(ParcelStoreRequest $request)
    {
        // FormRequest automatically validates
        $validated = $request->validated();

        $validated['tracking_number'] = Parcel::generateTrackingNumber();
        $validated['created_by'] = Auth::id();

        // Auto-assign logic
        if (!empty($validated['assigned_rider_id'])) {
            $validated['status_id'] = ParcelStatus::where('slug', 'assigned')->first()->id;
            $validated['assigned_at'] = now();
        } else {
            $validated['status_id'] = ParcelStatus::where('slug', 'pending')->first()->id;
        }

        $parcel = Parcel::create($validated);
        $this->syncRidersForParcel($parcel, null);

        return redirect()->route('admin.parcels.index')
            ->with('success', 'Parcel created successfully. Tracking #: ' . $parcel->tracking_number);
    }

    public function edit(Parcel $parcel)
    {
        $hubs = Hub::where('is_active', true)
            ->orderBy('name')
            ->get();
        $riders = Rider::with('user')
            ->where('hub_id', $parcel->source_hub_id)
            ->where(function ($query) use ($parcel) {
                $query->where('status', 'available');

                if ($parcel->assigned_rider_id) {
                    $query->orWhere('id', $parcel->assigned_rider_id);
                }
            })
            ->orderByDesc('rating')
            ->get();
        $statuses = ParcelStatus::all();

        return view('admin.parcels.edit', compact('parcel', 'hubs', 'riders', 'statuses'));
    }

    // public function edit(Parcel $parcel)
    // {
    //     // Load relationships
    //     $parcel->load(['assignedRider.user', 'sourceHub', 'destinationHub', 'status']);

    //     // Only get riders that have valid user accounts
    //     $riders = Rider::has('user')->with('user')->get();

    //     $hubs = Hub::where('is_active', true)->get();
    //     $statuses = ParcelStatus::all();

    //     return view('admin.parcels.edit', compact('parcel', 'riders', 'hubs', 'statuses'));
    // }

   /**
     * Update the specified parcel (Using FormRequest)
     */
    public function update(ParcelUpdateRequest $request, Parcel $parcel)
    {
        try {
            $validated = $request->validated();

            DB::transaction(function () use ($parcel, $validated, $request) {
                $previousRiderId = $parcel->assigned_rider_id;
                $previousStatusId = $parcel->status_id;
                $previousStatus = $parcel->status;

                // Handle failure reason for failed delivery
                if (isset($validated['status_id'])) {
                    $newStatus = ParcelStatus::find($validated['status_id']);

                    // If status is failed-delivery, store the failure reason
                    if ($newStatus->slug === 'failed-delivery' && $request->filled('failure_reason')) {
                        $validated['failure_reason'] = $request->failure_reason;
                        $validated['failed_delivery_at'] = now();
                        $validated['delivery_attempts'] = ($parcel->delivery_attempts ?? 0) + 1;
                    }

                    // Update timestamps based on new status
                    switch ($newStatus->slug) {
                        case 'assigned':
                            if (!$parcel->assigned_at) {
                                $validated['assigned_at'] = now();
                            }
                            break;
                        case 'picked-up':
                            $validated['picked_up_at'] = now();
                            break;
                        case 'out-for-delivery':
                            $validated['out_for_delivery_at'] = now();
                            break;
                        case 'delivered':
                            $validated['delivered_at'] = now();
                            break;
                        case 'returned-to-hub':
                            $validated['returned_at'] = now();
                            break;
                    }

                    // Create status history record if status changed
                    if ($validated['status_id'] != $previousStatusId) {
                        ParcelStatusHistory::create([
                            'parcel_id' => $parcel->id,
                            'status_id' => $newStatus->id,
                            'from_status_id' => $previousStatusId,
                            'notes' => $request->notes ?? $request->failure_reason,
                            'updated_by' => Auth::id(),
                        ]);
                    }
                }

                // Handle rider assignment
                $newRiderId = $validated['assigned_rider_id'] ?? null;
                $newStatus = isset($validated['status_id']) ? ParcelStatus::find($validated['status_id']) : null;

                if ($newRiderId && $newRiderId !== $previousRiderId && !$this->isTerminalStatus($newStatus?->slug)) {
                    $assignedStatus = ParcelStatus::where('slug', 'assigned')->first();
                    $validated['status_id'] = $assignedStatus?->id ?? $validated['status_id'] ?? $parcel->status_id;
                    $validated['assigned_at'] = $parcel->assigned_at ?? now();

                    // Send notification to new rider
                    $this->sendNotificationToRider($newRiderId, $parcel->tracking_number);
                }

                // Update the parcel
                $parcel->update($validated);

                // Sync rider statuses
                $this->syncRidersForParcel($parcel->fresh('status'), $previousRiderId);
            });

            return redirect()->route('admin.parcels.index')
                ->with('success', 'Parcel updated successfully');

        } catch (\Exception $e) {
            Log::error('Parcel update error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update parcel: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified parcel from storage (soft delete).
     */
    public function destroy(Parcel $parcel)
    {
        try {
            // Soft delete the parcel
            $parcel->delete();

            return redirect()->route('admin.parcels.index')
                ->with('success', 'Parcel moved to trash successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete parcel: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore a soft deleted parcel
     */
    public function restore($id)
    {
        try {
            $parcel = Parcel::withTrashed()->findOrFail($id);
            $parcel->restore();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Parcel restored successfully.'
                ]);
            }

            return redirect()->route('admin.parcels.trash')
                ->with('success', 'Parcel restored successfully.');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to restore parcel: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.parcels.trash')
                ->with('error', 'Failed to restore parcel.');
        }
    }
     /**
     * Permanently delete a soft deleted parcel
     */
    public function forceDelete($id)
    {
        try {
            $parcel = Parcel::withTrashed()->findOrFail($id);
            $parcel->forceDelete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Parcel permanently deleted.'
                ]);
            }

            return redirect()->route('admin.parcels.trash')
                ->with('success', 'Parcel permanently deleted.');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete parcel: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.parcels.trash')
                ->with('error', 'Failed to delete parcel.');
        }
    }

    /**
     * Bulk force delete parcels
     */
    public function bulkForceDelete(Request $request)
    {
        try {
            $ids = $request->ids;
            Parcel::onlyTrashed()->whereIn('id', $ids)->forceDelete();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' parcels permanently deleted.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique tracking number.
     */
    private function generateTrackingNumber()
    {
        $prefix = 'HLD';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        $trackingNumber = $prefix . $date . $random . $sequence;

        while (Parcel::where('tracking_number', $trackingNumber)->exists()) {
            $random = strtoupper(substr(uniqid(), -6));
            $trackingNumber = $prefix . $date . $random . $sequence;
        }

        return $trackingNumber;
    }


    /**
     * Send notification to rider when assigned
     */
    private function sendNotificationToRider($riderId, $trackingNumber)
    {
        $rider = Rider::with('user')->find($riderId);

        if ($rider && $rider->user) {
            Notification::create([
                'user_id' => $rider->user->id,
                'title' => 'New Parcel Assigned',
                'message' => "Parcel #{$trackingNumber} has been assigned to you. Please check your dashboard.",
                'type' => 'info',
                'is_read' => false,
            ]);
        }
    }

    /**
     * Find the best rider for auto-assignment (AJAX endpoint)
     */
    public function findBestRider(Request $request)
    {
        try {
            $request->validate([
                'weight' => 'required|numeric|min:0.1',
                'size' => 'required|numeric|min:0.1',
                'hub_id' => 'required|exists:hubs,id',
                'parcel_id' => 'required|exists:parcels,id'
            ]);

            $parcel = Parcel::find($request->parcel_id);
            
            // ✅ Check if parcel is already assigned
            if ($parcel->assigned_rider_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parcel is already assigned to a rider.',
                    'already_assigned' => true
                ], 200); // ← 200 OK, not 404
            }

            // Find best available rider
            $bestRider = Rider::where('status', 'available')
                ->where('hub_id', $parcel->source_hub_id)
                ->where(function($q) use ($request) {
                    $q->where('max_weight_capacity', '>=', $request->weight)
                    ->orWhereNull('max_weight_capacity');
                })
                ->where(function($q) use ($request) {
                    $q->where('max_size_capacity', '>=', $request->size)
                    ->orWhereNull('max_size_capacity');
                })
                ->orderBy('rating', 'desc')
                ->orderBy('total_deliveries', 'asc')
                ->with('user')
                ->first();

            if ($bestRider) {
                $assignedStatus = ParcelStatus::where('slug', 'assigned')->first();
                
                // Assign parcel to rider
                $parcel->assigned_rider_id = $bestRider->id;
                $parcel->status_id = $assignedStatus->id;
                $parcel->assigned_at = now();
                $parcel->save();
                
                // Update rider status to busy
                $bestRider->status = 'busy';
                $bestRider->save();
                
                // Send notification
                $this->sendNotificationToRider($bestRider->id, $parcel->tracking_number);

                // ✅ Return success with 200 status code
                return response()->json([
                    'success' => true,
                    'message' => "✓ Parcel #{$parcel->tracking_number} assigned to rider '{$bestRider->user->name}' successfully!",
                    'rider' => [
                        'id' => $bestRider->id,
                        'name' => $bestRider->user->name ?? 'Unknown',
                        'employee_id' => $bestRider->employee_id,
                        'max_weight_capacity' => $bestRider->max_weight_capacity,
                        'max_size_capacity' => $bestRider->max_size_capacity,
                        'rating' => $bestRider->rating,
                        'status' => $bestRider->status,
                    ],
                    'parcel' => [
                        'id' => $parcel->id,
                        'tracking_number' => $parcel->tracking_number,
                        'status' => 'assigned'
                    ]
                ], 200); // ← Explicit 200 OK
            }

            // ✅ No rider found - return 200 with success=false, NOT 404
            return response()->json([
                'success' => false,
                'message' => 'No available rider found in this hub with sufficient capacity. Please check: (1) Riders exist in this hub, (2) Rider status is "available", (3) Rider has enough capacity.',
                'debug_info' => [
                    'hub_id' => $parcel->source_hub_id,
                    'weight' => $request->weight,
                    'size' => $request->size
                ]
            ], 200); // ← 200 OK, not 404

        } catch (\Exception $e) {
            Log::error('Auto assign error: ' . $e->getMessage());
            
            // ✅ Error response with 500 status code
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Auto-assign all pending parcels
     */
    public function autoAssignAll()
    {
        $pendingParcels = Parcel::where('status_id', ParcelStatus::where('slug', 'pending')->first()->id)
            ->whereNull('assigned_rider_id')
            ->get();

        $assignedCount = 0;
        $failedCount = 0;

        foreach ($pendingParcels as $parcel) {
            $bestRider = Rider::findBestRiderForParcel(
                $parcel->weight,
                $parcel->size,
                $parcel->source_hub_id
            );

            if ($bestRider) {
                $parcel->assigned_rider_id = $bestRider->id;
                $parcel->status_id = ParcelStatus::where('slug', 'assigned')->first()->id;
                $parcel->assigned_at = now();
                $parcel->save();

                $bestRider->status = 'busy';
                $bestRider->save();
                $this->syncRidersForParcel($parcel->fresh('status'), null);

                $this->sendNotificationToRider($bestRider->id, $parcel->tracking_number);
                $assignedCount++;
            } else {
                $failedCount++;
            }
        }

        return redirect()->route('admin.parcels.index')
            ->with('success', "Auto-assignment complete! Assigned: {$assignedCount}, Failed: {$failedCount}");
    }

    /**
     * Parcels DataTable - Server Side
     */
    public function getDataTable(Request $request)
    {
        if ($request->ajax()) {
            $parcels = Parcel::with(['status', 'assignedRider.user', 'sourceHub'])
                ->select('parcels.*');

            return DataTables::of($parcels)
                ->addColumn('status_badge', function($parcel) {
                    $color = $parcel->status->color_code ?? '#6c757d';
                    return '<span class="badge" style="background-color: ' . $color . '; color: white; padding: 5px 10px;">'
                        . ($parcel->status->display_name ?? 'Unknown') . '</span>';
                })
                ->addColumn('rider_name', function($parcel) {
                    return $parcel->assignedRider->user->name ?? '<span class="text-muted">Unassigned</span>';
                })
                ->addColumn('created_date', function($parcel) {
                    return $parcel->created_at->format('d M Y');
                })
                ->addColumn('action', function($parcel) {
                    return '
                        <div class="btn-group" role="group">
                            <a href="' . route('admin.parcels.show', $parcel->id) . '" class="btn btn-sm btn-info" title="View">
                                <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                            </a>
                            <a href="' . route('admin.parcels.edit', $parcel->id) . '" class="btn btn-sm btn-warning" title="Edit">
                                <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="confirmDelete(' . $parcel->id . ')">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                            </button>
                        </div>
                    ';
                })
                ->editColumn('weight', function($parcel) {
                    return $parcel->weight . ' kg';
                })
                ->rawColumns(['status_badge', 'rider_name', 'action'])
                ->make(true);
        }

        return view('admin.parcels.datatable');
    }

    /**
     * Display the specified parcel.
     */
    public function show($id)
    {
        $parcel = Parcel::with(['status', 'assignedRider.user', 'sourceHub', 'statusHistories.updater'])
            ->findOrFail($id);

        return view('admin.parcels.show', compact('parcel'));
    }

    private function syncRidersForParcel(Parcel $parcel, $previousRiderId = null): void
    {
        if ($previousRiderId && $previousRiderId !== $parcel->assigned_rider_id) {
            Rider::find($previousRiderId)?->syncStatusWithAssignments();
        }

        if ($parcel->assigned_rider_id) {
            Rider::find($parcel->assigned_rider_id)?->syncStatusWithAssignments();
        }
    }

    private function isTerminalStatus(?string $slug): bool
    {
        return in_array($slug, ['delivered', 'cancelled', 'returned-to-hub', 'returned-to-sender'], true);
    }

    /**
     * Update only the parcel status (from admin panel)
     * This is separate from the full parcel update
     */
    public function updateStatus(Request $request, Parcel $parcel)
    {
        try {
            // Manual validation for status update
            $validated = $request->validate([
                'status_id' => 'required|exists:parcel_statuses,id',
                'notes' => 'nullable|string|max:500',
                'failure_reason' => 'nullable|string|max:255',
            ]);

            $oldStatusId = $parcel->status_id;
            $oldStatus = $parcel->status;
            $newStatus = ParcelStatus::find($request->status_id);

            if (!$newStatus) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Invalid status selected'], 400);
                }
                return redirect()->back()->with('error', 'Invalid status selected');
            }

            // If status is "failed-delivery", failure reason is required
            if ($newStatus->slug === 'failed-delivery' && empty($request->failure_reason)) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Failure reason is required for failed delivery'], 422);
                }
                return redirect()->back()->with('error', 'Failure reason is required for failed delivery');
            }

            DB::beginTransaction();

            // Update the parcel status
            $parcel->status_id = $newStatus->id;

            // Update timestamps based on new status
            switch ($newStatus->slug) {
                case 'assigned':
                    if (!$parcel->assigned_at) {
                        $parcel->assigned_at = now();
                    }
                    break;
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
                    $parcel->delivery_attempts = ($parcel->delivery_attempts ?? 0) + 1;
                    $parcel->failure_reason = $request->failure_reason;
                    break;
                case 'returned-to-hub':
                    $parcel->returned_at = now();
                    break;
                case 'cancelled':
                    $parcel->cancelled_at = now();
                    break;
            }

            $parcel->save();

            // Create history record
            ParcelStatusHistory::create([
                'parcel_id' => $parcel->id,
                'status_id' => $newStatus->id,
                'from_status_id' => $oldStatusId,
                'notes' => $request->notes ?? $request->failure_reason,
                'updated_by' => Auth::id(),
            ]);

            // If status is delivered or failed, update rider statistics
            if ($parcel->assigned_rider_id && in_array($newStatus->slug, ['delivered', 'failed-delivery', 'returned-to-hub'])) {
                $rider = Rider::find($parcel->assigned_rider_id);
                if ($rider) {
                    if ($newStatus->slug === 'delivered') {
                        $rider->successful_deliveries = ($rider->successful_deliveries ?? 0) + 1;
                        $rider->total_deliveries = ($rider->total_deliveries ?? 0) + 1;
                        $rider->earnings = ($rider->earnings ?? 0) + ($parcel->delivery_charge * 0.7);
                        $rider->status = 'available';
                    } elseif ($newStatus->slug === 'failed-delivery') {
                        $rider->failed_deliveries = ($rider->failed_deliveries ?? 0) + 1;
                        $rider->total_deliveries = ($rider->total_deliveries ?? 0) + 1;
                    } elseif ($newStatus->slug === 'returned-to-hub') {
                        $rider->status = 'available';
                    }
                    $rider->save();
                    $rider->syncStatusWithAssignments();
                }
            }

            DB::commit();

            // Send notification to rider if needed
            if ($parcel->assigned_rider_id && in_array($newStatus->slug, ['assigned', 'picked-up', 'out-for-delivery'])) {
                $this->sendStatusNotificationToRider($parcel, $newStatus);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated to: ' . $newStatus->display_name,
                    'parcel' => [
                        'id' => $parcel->id,
                        'tracking_number' => $parcel->tracking_number,
                        'status' => [
                            'id' => $newStatus->id,
                            'display_name' => $newStatus->display_name,
                            'slug' => $newStatus->slug,
                            'color_code' => $newStatus->color_code,
                        ]
                    ]
                ]);
            }

            return redirect()->route('admin.parcels.show', $parcel->id)
                ->with('success', 'Parcel status updated to: ' . $newStatus->display_name);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status update error: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to rider about status change
     */
    private function sendStatusNotificationToRider($parcel, $newStatus)
    {
        $rider = Rider::with('user')->find($parcel->assigned_rider_id);

        if ($rider && $rider->user) {
            $messages = [
                'assigned' => "New parcel #{$parcel->tracking_number} has been assigned to you.",
                'picked-up' => "Parcel #{$parcel->tracking_number} has been marked as picked up.",
                'out-for-delivery' => "Parcel #{$parcel->tracking_number} is out for delivery.",
                'delivered' => "Parcel #{$parcel->tracking_number} has been delivered. Well done!",
                'failed-delivery' => "Delivery failed for parcel #{$parcel->tracking_number}. Reason: {$parcel->failure_reason}",
                'returned-to-hub' => "Parcel #{$parcel->tracking_number} has been returned to hub.",
            ];

            $message = $messages[$newStatus->slug] ?? "Parcel #{$parcel->tracking_number} status updated to: {$newStatus->display_name}";

            Notification::create([
                'user_id' => $rider->user->id,
                'title' => 'Parcel Status Update',
                'message' => $message,
                'type' => $newStatus->slug === 'delivered' ? 'success' : 'info',
                'is_read' => false,
            ]);
        }
    }

    /**
     * Quick update parcel status only (AJAX endpoint for admin)
     */
    public function quickUpdateStatus(Request $request, Parcel $parcel)
    {
        try {
            $request->validate([
                'status_id' => 'required|exists:parcel_statuses,id',
                'failure_reason' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:500',
            ]);

            $oldStatusId = $parcel->status_id;
            $oldStatus = $parcel->status;
            $newStatus = ParcelStatus::find($request->status_id);

            if (!$newStatus) {
                return response()->json(['error' => 'Invalid status selected'], 400);
            }

            // Validate failure reason for failed delivery
            if ($newStatus->slug === 'failed-delivery' && empty($request->failure_reason)) {
                return response()->json(['error' => 'Failure reason is required for failed delivery'], 422);
            }

            DB::beginTransaction();

            // Update status
            $parcel->status_id = $newStatus->id;

            // Update timestamps based on status
            switch ($newStatus->slug) {
                case 'assigned':
                    if (!$parcel->assigned_at) {
                        $parcel->assigned_at = now();
                    }
                    break;
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
                    $parcel->delivery_attempts = ($parcel->delivery_attempts ?? 0) + 1;
                    $parcel->failure_reason = $request->failure_reason;
                    break;
                case 'returned-to-hub':
                    $parcel->returned_at = now();
                    break;
                case 'cancelled':
                    $parcel->cancelled_at = now();
                    break;
            }

            $parcel->save();

            // Create status history
            ParcelStatusHistory::create([
                'parcel_id' => $parcel->id,
                'status_id' => $newStatus->id,
                'from_status_id' => $oldStatusId,
                'notes' => $request->notes ?? $request->failure_reason,
                'updated_by' => Auth::id(),
            ]);

            // Update rider statistics if needed
            if ($parcel->assigned_rider_id && in_array($newStatus->slug, ['delivered', 'failed-delivery', 'returned-to-hub'])) {
                $rider = Rider::find($parcel->assigned_rider_id);
                if ($rider) {
                    if ($newStatus->slug === 'delivered') {
                        $rider->successful_deliveries = ($rider->successful_deliveries ?? 0) + 1;
                        $rider->total_deliveries = ($rider->total_deliveries ?? 0) + 1;
                        $rider->earnings = ($rider->earnings ?? 0) + ($parcel->delivery_charge * 0.7);
                        $rider->status = 'available';
                    } elseif ($newStatus->slug === 'failed-delivery') {
                        $rider->failed_deliveries = ($rider->failed_deliveries ?? 0) + 1;
                        $rider->total_deliveries = ($rider->total_deliveries ?? 0) + 1;
                    } elseif ($newStatus->slug === 'returned-to-hub') {
                        $rider->status = 'available';
                    }
                    $rider->save();
                    $rider->syncStatusWithAssignments();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status updated to: ' . $newStatus->display_name,
                'parcel' => [
                    'id' => $parcel->id,
                    'tracking_number' => $parcel->tracking_number,
                    'status' => [
                        'id' => $newStatus->id,
                        'display_name' => $newStatus->display_name,
                        'slug' => $newStatus->slug,
                        'color_code' => $newStatus->color_code,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quick status update error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
}
