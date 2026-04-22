<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\ParcelStatus;
use App\Models\Notification;
use App\Models\User;
use App\Models\Payment;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class RiderController extends Controller
{
    /**
     * Get authenticated rider's ID
     */
    private function getRiderId()
    {
        $rider = Auth::user()->rider;
        if (!$rider) {
            abort(403, 'Rider profile not found');
        }
        return $rider->id;
    }

    /**
     * Rider Dashboard
     */
    public function dashboard()
    {
        $riderId = $this->getRiderId();
        $rider = Auth::user()->rider;

        $totalDeliveries = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })->count();

        $successfulDeliveries = $rider->successful_deliveries;
        $failedDeliveries = $rider->failed_deliveries;

        $successRate = $totalDeliveries > 0 ? round(($successfulDeliveries / $totalDeliveries) * 100, 2) : 0;

        $totalEarnings = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->sum(DB::raw('delivery_charge * 0.7'));

        $activeParcels = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->whereNotIn('slug', ['delivered', 'cancelled', 'returned_to_sender']);
            })
            ->with('status')
            ->orderBy('created_at', 'desc')
            ->get();

        $recentDeliveries = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->with('status')
            ->orderBy('delivered_at', 'desc')
            ->limit(10)
            ->get();

        $todaysDeliveries = Parcel::where('assigned_rider_id', $riderId)
            ->whereDate('delivered_at', today())
            ->count();

        $weeklyEarnings = Parcel::where('assigned_rider_id', $riderId)
            ->where('delivered_at', '>=', now()->startOfWeek())
            ->select(DB::raw('DATE(delivered_at) as date'), DB::raw('SUM(delivery_charge * 0.7) as total'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        return view('rider.dashboard', compact(
            'totalDeliveries', 'successfulDeliveries', 'failedDeliveries',
            'successRate', 'totalEarnings', 'activeParcels', 'recentDeliveries',
            'todaysDeliveries', 'weeklyEarnings'
        ));
    }

    /**
     * Display rider's parcels page
     */
    public function parcels()
    {
        $statuses = ParcelStatus::where('is_rider_updatable', true)
            ->orWhereIn('slug', ['delivered', 'failed-delivery', 'returned-to-hub', 'assigned'])
            ->orderBy('sequence_order')
            ->get();

        return view('rider.parcels', compact('statuses'));
    }

    /**
     * Get rider's parcels data for DataTable
     */
    public function getParcelsData(Request $request)
    {
        try {
            $riderId = $this->getRiderId();

            $parcels = Parcel::with(['status', 'sourceHub'])
                ->where('assigned_rider_id', $riderId)
                ->select('parcels.*');

            return DataTables::eloquent($parcels)
                ->editColumn('weight', function($parcel) {
                    return $parcel->weight . ' kg';
                })
                ->addColumn('receiver_info', function($parcel) {
                    return '<strong>' . e($parcel->receiver_name) . '</strong><br>
                            <small class="text-muted">' . e($parcel->receiver_phone) . '</small>';
                })
                ->addColumn('address_short', function($parcel) {
                    return \Illuminate\Support\Str::limit(e($parcel->receiver_address), 40);
                })
                ->addColumn('status_badge', function($parcel) {
                    $color = $parcel->status->color_code ?? '#6c757d';
                    return '<span class="badge" style="background-color: ' . $color . '; color: white; padding: 5px 10px;">'
                        . e($parcel->status->display_name ?? 'Unknown') . '</span>';
                })
                ->addColumn('action', function($parcel) {
                    $canUpdate = in_array($parcel->status->slug, ['assigned', 'picked-up', 'out-for-delivery', 'failed-delivery']);

                    if ($canUpdate) {
                        return '<button type="button" class="btn btn-sm btn-primary update-status-btn"
                                    data-parcel-id="' . $parcel->id . '"
                                    data-tracking="' . e($parcel->tracking_number) . '"
                                    data-current-status="' . e($parcel->status->slug) . '"
                                    data-current-status-name="' . e($parcel->status->display_name) . '"
                                    data-bs-toggle="modal"
                                    data-bs-target="#updateStatusModal">
                                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon> Update
                                </button>';
                    }
                    return '<button class="btn btn-sm btn-secondary" disabled>
                                <iconify-icon icon="solar:lock-line-duotone"></iconify-icon> Completed
                            </button>';
                })
                ->rawColumns(['receiver_info', 'status_badge', 'action'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('DataTable error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update parcel status
     */
    public function updateParcelStatus(Request $request, Parcel $parcel)
    {
        $riderId = $this->getRiderId();

        if ($parcel->assigned_rider_id !== $riderId) {
            return response()->json(['error' => 'Unauthorized - This parcel is not assigned to you'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status_id' => 'required|exists:parcel_statuses,id',
            'failure_reason' => 'required_if:status_id,6|nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newStatus = ParcelStatus::find($request->status_id);

        if (!$this->canUpdateStatus($parcel, $newStatus)) {
            return response()->json(['error' => 'Invalid status transition'], 400);
        }

        DB::beginTransaction();

        try {
            $oldStatusId = $parcel->status_id;
            $parcel->status_id = $newStatus->id;

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
                'updated_by' => Auth::id(),
            ]);

            $rider = Auth::user()->rider;

            if ($newStatus->slug === 'delivered') {
                $rider->successful_deliveries++;
                $rider->total_deliveries++;
                $rider->earnings = ($rider->earnings ?? 0) + ($parcel->delivery_charge * 0.7);
                $rider->status = 'available';
                $rider->save();

                if ($parcel->payment_method === 'cash' && $parcel->payment_status !== 'paid') {
                    Payment::create([
                        'parcel_id' => $parcel->id,
                        'amount' => $parcel->delivery_charge,
                        'payment_method' => 'cash',
                        'payment_status' => 'completed',
                        'collected_by' => Auth::id(),
                        'collected_at' => now(),
                    ]);
                    $parcel->payment_status = 'paid';
                    $parcel->save();
                }

                $this->sendNotificationToAdmins('✅ Parcel Delivered', "Parcel #{$parcel->tracking_number} delivered by {$rider->user->name}", 'success');

            } elseif ($newStatus->slug === 'failed-delivery') {
                $rider->failed_deliveries++;
                $rider->total_deliveries++;
                $rider->save();

                $this->sendNotificationToAdmins('❌ Delivery Failed', "Parcel #{$parcel->tracking_number} failed. Reason: {$request->failure_reason}", 'error');

            } elseif ($newStatus->slug === 'returned-to-hub') {
                $rider->status = 'available';
                $rider->save();

                $this->sendNotificationToAdmins('🔄 Parcel Returned', "Parcel #{$parcel->tracking_number} returned to hub by {$rider->user->name}", 'warning');

            } elseif ($newStatus->slug === 'picked-up') {
                $this->sendNotificationToAdmins('📦 Parcel Picked Up', "Parcel #{$parcel->tracking_number} picked up by {$rider->user->name}", 'info');

            } elseif ($newStatus->slug === 'out-for-delivery') {
                $this->sendNotificationToAdmins('🚚 Out for Delivery', "Parcel #{$parcel->tracking_number} is out for delivery with {$rider->user->name}", 'info');
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
                        'color_code' => $newStatus->color_code,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get available statuses for a parcel
     */
    public function getAvailableStatuses(Parcel $parcel)
    {
        $riderId = $this->getRiderId();

        if ($parcel->assigned_rider_id !== $riderId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $currentStatusSlug = $parcel->status ? $parcel->status->slug : 'pending';

        $allowedStatusSlugs = [];

        switch ($currentStatusSlug) {
            case 'assigned':
                $allowedStatusSlugs = ['picked-up'];
                break;
            case 'picked-up':
                $allowedStatusSlugs = ['out-for-delivery', 'returned-to-hub'];
                break;
            case 'out-for-delivery':
                $allowedStatusSlugs = ['delivered', 'failed-delivery', 'returned-to-hub'];
                break;
            case 'failed-delivery':
                $allowedStatusSlugs = ['out-for-delivery', 'returned-to-hub'];
                break;
            default:
                $allowedStatusSlugs = [];
        }

        $availableStatuses = ParcelStatus::whereIn('slug', $allowedStatusSlugs)->get();

        return response()->json($availableStatuses);
    }

    /**
     * Check if status update is allowed
     */
    private function canUpdateStatus($parcel, $newStatus)
    {
        if (!$newStatus->is_rider_updatable) {
            return false;
        }

        $allowedTransitions = [
            'assigned' => ['picked-up'],
            'picked-up' => ['out-for-delivery', 'returned-to-hub'],
            'out-for-delivery' => ['delivered', 'failed-delivery', 'returned-to-hub'],
            'failed-delivery' => ['out-for-delivery', 'returned-to-hub'],
        ];

        $currentStatusSlug = $parcel->status ? $parcel->status->slug : 'pending';
        $newStatusSlug = $newStatus->slug;

        if ($currentStatusSlug === 'pending') {
            return false;
        }

        return isset($allowedTransitions[$currentStatusSlug]) &&
               in_array($newStatusSlug, $allowedTransitions[$currentStatusSlug]);
    }

    /**
     * Send notification to all admins
     */
    private function sendNotificationToAdmins($title, $message, $type = 'info')
    {
        $admins = User::whereHas('role', function($q) {
            $q->where('slug', 'admin');
        })->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'is_read' => false,
            ]);
        }
    }

    /**
     * Display rider earnings page
     */
    public function earnings(Request $request)
    {
        $riderId = $this->getRiderId();
        $rider = Auth::user()->rider;

        $period = $request->get('period', 'monthly');
        list($startDate, $endDate) = $this->getDateRange($period, $request);

        $totalEarnings = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->sum(DB::raw('delivery_charge * 0.7'));

        $periodEarnings = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->sum('delivery_charge');

        $deliveriesCount = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->count();

        $commissionEarnings = $periodEarnings * 0.7;
        $dailyEarnings = Parcel::getDailyEarningsForRider($riderId, $startDate, $endDate);
        $earningsHistory = Parcel::getEarningsHistoryForRider($riderId);

        return view('rider.earnings', compact(
            'totalEarnings', 'periodEarnings', 'commissionEarnings',
            'deliveriesCount', 'dailyEarnings', 'earningsHistory',
            'period', 'startDate', 'endDate'
        ));
    }

    /**
     * Display rider profile
     */
    public function profile()
    {
        $rider = Auth::user()->rider;
        $user = Auth::user();
        return view('rider.profile', compact('rider', 'user'));
    }

    /**
     * Update rider profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'vehicle_number' => 'nullable|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $rider = $user->rider;

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $rider->update([
            'vehicle_number' => $request->vehicle_number,
            'vehicle_model' => $request->vehicle_model,
        ]);

        return redirect()->route('rider.profile')->with('success', 'Profile updated successfully');
    }

    /**
     * Update rider status (available/busy/offline)
     */
    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,busy,offline'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $rider = Auth::user()->rider;
        $oldStatus = $rider->status;

        $rider->status = $request->status;
        $rider->save();

        $this->sendNotificationToAdmins(
            'Rider Status Changed',
            "Rider {$rider->user->name} changed status from " . ucfirst($oldStatus) . " to " . ucfirst($request->status),
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'status' => $rider->status
        ]);
    }

    /**
     * Update profile image
     */
    public function updateProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $user = Auth::user();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && file_exists(storage_path('app/public/' . $user->profile_image))) {
                unlink(storage_path('app/public/' . $user->profile_image));
            }

            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $imagePath;
            $user->save();

            return redirect()->route('rider.profile')->with('success', 'Profile picture updated!');
        }

        return redirect()->route('rider.profile')->with('error', 'Failed to update profile picture');
    }

    /**
     * Get date range based on period
     */
    private function getDateRange($period, $request)
    {
        if ($period === 'custom' && $request->get('start_date') && $request->get('end_date')) {
            return [
                \Carbon\Carbon::parse($request->get('start_date'))->startOfDay(),
                \Carbon\Carbon::parse($request->get('end_date'))->endOfDay()
            ];
        }

        switch ($period) {
            case 'daily':
                return [now()->startOfDay(), now()->endOfDay()];
            case 'weekly':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'yearly':
                return [now()->startOfYear(), now()->endOfYear()];
            default:
                return [now()->startOfMonth(), now()->endOfMonth()];
        }
    }
}
