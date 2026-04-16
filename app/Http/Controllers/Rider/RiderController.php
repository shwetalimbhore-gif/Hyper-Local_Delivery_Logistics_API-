<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\ParcelStatus;
use App\Models\ParcelStatusHistory;
use App\Models\Payment;
use App\Models\Rider as RiderModel;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RiderController extends Controller
{
    /**
     * Get the authenticated rider's ID
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
     * Rider Dashboard - Only shows data for logged-in rider
     */
    public function dashboard()
    {
        $riderId = $this->getRiderId();
        $rider = Auth::user()->rider;

        // Get counts only for this rider
        $totalDeliveries = Parcel::where('assigned_rider_id', $riderId)->count();
        $successfulDeliveries = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })->count();
        $failedDeliveries = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'failed-delivery');
            })->count();

        $successRate = $totalDeliveries > 0 ? round(($successfulDeliveries / $totalDeliveries) * 100, 2) : 0;
        $totalEarnings = $rider->earnings ?? 0;

        // Active parcels for this rider only
        $activeParcels = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->whereNotIn('slug', ['delivered', 'cancelled', 'returned_to_sender']);
            })
            ->with('status')
            ->orderBy('created_at', 'desc')
            ->get();

        // Recent deliveries for this rider only
        $recentDeliveries = Parcel::where('assigned_rider_id', $riderId)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->with('status')
            ->orderBy('delivered_at', 'desc')
            ->limit(10)
            ->get();

        // Today's deliveries for this rider only
        $todaysDeliveries = Parcel::where('assigned_rider_id', $riderId)
            ->whereDate('delivered_at', today())
            ->count();

        // Weekly earnings for this rider only
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
     * Display rider's parcels - ONLY assigned to this rider
     */
    public function parcels(Request $request)
    {
        $riderId = $this->getRiderId();
        $statusFilter = $request->get('status');

        // Query only parcels assigned to this rider ID
        $parcels = Parcel::where('assigned_rider_id', $riderId)
            ->with(['status', 'sourceHub'])
            ->when($statusFilter, function($q) use ($statusFilter) {
                return $q->whereHas('status', function($sq) use ($statusFilter) {
                    $sq->where('slug', $statusFilter);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $statuses = ParcelStatus::where('is_rider_updatable', true)->get();

        return view('rider.parcels', compact('parcels', 'statuses', 'statusFilter'));
    }

    /**
     * Update parcel status - ONLY if assigned to this rider
     */
    public function updateParcelStatus(Request $request, Parcel $parcel)
    {
        $riderId = $this->getRiderId();

        // CRITICAL: Check if parcel belongs to this rider
        if ($parcel->assigned_rider_id !== $riderId) {
            return response()->json([
                'error' => 'Unauthorized - This parcel is not assigned to you'
            ], 403);
        }

        $request->validate([
            'status_id' => 'required|exists:parcel_statuses,id',
            'failure_reason' => 'required_if:status_id,6|nullable|string',
            'notes' => 'nullable|string',
        ]);

        $newStatus = ParcelStatus::find($request->status_id);
        $oldStatus = $parcel->status;

        // Check if status transition is allowed
        if (!$this->canUpdateStatus($parcel, $newStatus)) {
            return response()->json([
                'error' => 'Invalid status transition from ' . ($oldStatus->display_name ?? 'Unknown') . ' to ' . $newStatus->display_name
            ], 400);
        }

        DB::beginTransaction();

        try {
            $oldStatusId = $parcel->status_id;
            $parcel->status_id = $newStatus->id;

            // Update timestamps based on status
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
            ParcelStatusHistory::create([
                'parcel_id' => $parcel->id,
                'status_id' => $newStatus->id,
                'from_status_id' => $oldStatusId,
                'notes' => $request->notes ?? $request->failure_reason,
                'updated_by' => Auth::id(),
            ]);

            // Update rider statistics
            $rider = Auth::user()->rider;

            if ($newStatus->slug === 'delivered') {
                $rider->successful_deliveries++;
                $rider->total_deliveries++;
                $rider->earnings = ($rider->earnings ?? 0) + ($parcel->delivery_charge * 0.7);
                $rider->status = 'available';
                $rider->save();

                // Create payment record if cash on delivery
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

                $this->sendNotificationToAdmins(
                    '✅ Parcel Delivered',
                    "Parcel #{$parcel->tracking_number} delivered by {$rider->user->name}",
                    'success'
                );

            } elseif ($newStatus->slug === 'failed-delivery') {
                $rider->failed_deliveries++;
                $rider->total_deliveries++;
                $rider->save();

                $this->sendNotificationToAdmins(
                    '❌ Delivery Failed',
                    "Parcel #{$parcel->tracking_number} failed. Reason: {$request->failure_reason}",
                    'error'
                );

            } elseif ($newStatus->slug === 'returned-to-hub') {
                $rider->status = 'available';
                $rider->save();

                $this->sendNotificationToAdmins(
                    '🔄 Parcel Returned',
                    "Parcel #{$parcel->tracking_number} returned to hub by {$rider->user->name}",
                    'warning'
                );
            } elseif ($newStatus->slug === 'picked-up') {
                $this->sendNotificationToAdmins(
                    '📦 Parcel Picked Up',
                    "Parcel #{$parcel->tracking_number} picked up by {$rider->user->name}",
                    'info'
                );
            } elseif ($newStatus->slug === 'out-for-delivery') {
                $this->sendNotificationToAdmins(
                    '🚚 Out for Delivery',
                    "Parcel #{$parcel->tracking_number} is out for delivery with {$rider->user->name}",
                    'info'
                );
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
     * Get available statuses - ONLY for parcels assigned to this rider
     */
    public function getAvailableStatuses(Parcel $parcel)
    {
        $riderId = $this->getRiderId();

        // CRITICAL: Check if parcel belongs to this rider
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
     * Display rider earnings - ONLY for this rider
     */
    public function earnings(Request $request)
    {
        $riderId = $this->getRiderId();
        $rider = Auth::user()->rider;

        $period = $request->get('period', 'monthly');

        switch ($period) {
            case 'weekly':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'monthly':
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'yearly':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            default:
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
        }

        // Only this rider's earnings from deliveries
        $deliveryEarnings = Parcel::where('assigned_rider_id', $riderId)
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->sum('delivery_charge');

        // Payments collected by this rider (cash on delivery)
        $paymentsCollected = Payment::where('collected_by', Auth::id())
            ->whereBetween('collected_at', [$startDate, $endDate])
            ->sum('amount');

        // Daily earnings chart data
        $dailyEarnings = Parcel::where('assigned_rider_id', $riderId)
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(delivered_at) as date'), DB::raw('SUM(delivery_charge * 0.7) as total'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Earnings history with pagination
        $earningsHistory = Parcel::where('assigned_rider_id', $riderId)
            ->whereNotNull('delivered_at')
            ->with(['status', 'sourceHub'])
            ->orderBy('delivered_at', 'desc')
            ->paginate(15);

        // Total earnings from rider table
        $totalEarnings = $rider->earnings ?? 0;

        return view('rider.earnings', compact(
            'totalEarnings',
            'deliveryEarnings',
            'paymentsCollected',  // Make sure this is included
            'dailyEarnings',
            'earningsHistory',
            'period',
            'startDate',
            'endDate'
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
        $user = Auth::user();
        $rider = $user->rider;

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'vehicle_number' => 'nullable|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
        ]);

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
     * Update rider profile image
     */
    public function updateProfileImage(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && file_exists(storage_path('app/public/' . $user->profile_image))) {
                unlink(storage_path('app/public/' . $user->profile_image));
            }

            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $imagePath;
            $user->save();

            return redirect()->route('rider.profile')->with('success', 'Profile picture updated successfully!');
        }

        return redirect()->route('rider.profile')->with('error', 'Failed to update profile picture.');
    }

    /**
     * Update rider status (available/busy/offline)
     */
    public function updateStatus(Request $request)
    {
        $rider = Auth::user()->rider;
        $oldStatus = $rider->status;

        $request->validate([
            'status' => 'required|in:available,busy,offline'
        ]);

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
}
