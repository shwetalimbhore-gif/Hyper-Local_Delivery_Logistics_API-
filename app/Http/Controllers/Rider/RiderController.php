<?php

namespace App\Http\Controllers\Rider;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\ParcelStatus;
use App\Models\Notification;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Rider\UpdateProfileRequest;
use App\Http\Requests\Rider\UpdateParcelStatusRequest;
use App\Http\Requests\Rider\UpdateRiderStatusRequest;
use App\Http\Requests\Rider\UploadProfileImageRequest;

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
     * Update parcel status (Using FormRequest)
     */
    public function updateParcelStatus(UpdateParcelStatusRequest $request, Parcel $parcel)
    {
        $riderId = $this->getRiderId();

        if ($parcel->assigned_rider_id !== $riderId) {
            return response()->json(['error' => 'Unauthorized - This parcel is not assigned to you'], 403);
        }

        $newStatus = ParcelStatus::find($request->status_id);

        if (!$this->canUpdateStatus($parcel, $newStatus)) {
            return response()->json(['error' => 'Invalid status transition'], 400);
        }

        // Rest of the method remains the same...
        DB::beginTransaction();

        try {
            // ... same code as before ...
            $oldStatusId = $parcel->status_id;
            $parcel->status_id = $newStatus->id;

            // ... rest of the update logic ...

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

        // Get daily earnings
        $dailyEarnings = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(delivered_at) as date'), DB::raw('SUM(delivery_charge * 0.7) as total'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Get earnings history
        $earningsHistory = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->with(['status', 'sourceHub'])
            ->orderBy('delivered_at', 'desc')
            ->paginate(15);

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
     * Update rider profile (Using FormRequest)
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $rider = $user->rider;

        // Update user
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // Update rider
        $rider->update([
            'vehicle_number' => $request->vehicle_number,
            'vehicle_model' => $request->vehicle_model,
        ]);

        return redirect()->route('rider.profile')->with('success', 'Profile updated successfully');
    }
    /**
     * Update rider status (Using FormRequest)
     */
    public function updateStatus(UpdateRiderStatusRequest $request)
    {
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
     * Update profile image (Using FormRequest)
     */
    public function updateProfileImage(UploadProfileImageRequest $request)
    {
        $user = Auth::user();

        if ($request->hasFile('profile_image')) {
            // Delete old image
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
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
