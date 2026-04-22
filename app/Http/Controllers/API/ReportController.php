<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Models\Rider;
use App\Models\Payment;
use App\Models\Hub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Get earnings report (admin only)
     * GET /api/reports/earnings
     */
    public function earnings(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $hubId = $request->get('hub_id');

        $query = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate, $endDate]);

        if ($hubId) {
            $query->where('source_hub_id', $hubId);
        }

        $totalEarnings = $query->sum(DB::raw('delivery_charge * 0.7'));
        $totalDeliveries = $query->count();
        $totalCharges = $query->sum('delivery_charge');

        // Earnings by hub
        $earningsByHub = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->join('hubs', 'parcels.source_hub_id', '=', 'hubs.id')
            ->select('hubs.name', DB::raw('COUNT(parcels.id) as deliveries'), DB::raw('SUM(parcels.delivery_charge * 0.7) as earnings'))
            ->groupBy('hubs.id', 'hubs.name')
            ->get();

        // Top riders
        $topRiders = Parcel::whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->join('riders', 'parcels.assigned_rider_id', '=', 'riders.id')
            ->join('users', 'riders.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(parcels.id) as deliveries'), DB::raw('SUM(parcels.delivery_charge * 0.7) as earnings'))
            ->groupBy('riders.id', 'users.name')
            ->orderBy('earnings', 'DESC')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_earnings' => $totalEarnings,
                    'total_deliveries' => $totalDeliveries,
                    'total_charges' => $totalCharges,
                    'average_commission' => $totalDeliveries > 0 ? $totalEarnings / $totalDeliveries : 0,
                ],
                'by_hub' => $earningsByHub,
                'top_riders' => $topRiders,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
            ]
        ]);
    }

    /**
     * Get delivery report (admin only)
     * GET /api/reports/delivery
     */
    public function delivery(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $hubId = $request->get('hub_id');

        $query = Parcel::whereBetween('created_at', [$startDate, $endDate]);

        if ($hubId) {
            $query->where('source_hub_id', $hubId);
        }

        $totalParcels = $query->count();
        $deliveredCount = (clone $query)->whereHas('status', function($q) {
            $q->where('slug', 'delivered');
        })->count();
        $failedCount = (clone $query)->whereHas('status', function($q) {
            $q->where('slug', 'failed-delivery');
        })->count();

        // Status distribution
        $statusDistribution = \App\Models\ParcelStatus::withCount(['parcels' => function($q) use ($startDate, $endDate, $hubId) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
            if ($hubId) {
                $q->where('source_hub_id', $hubId);
            }
        }])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_parcels' => $totalParcels,
                    'delivered' => $deliveredCount,
                    'failed' => $failedCount,
                    'success_rate' => $totalParcels > 0 ? round(($deliveredCount / $totalParcels) * 100, 2) : 0,
                ],
                'status_distribution' => $statusDistribution,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
            ]
        ]);
    }
}
