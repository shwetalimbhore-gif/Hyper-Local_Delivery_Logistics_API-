<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RiderController extends Controller
{
    /**
     * Get my assigned parcels
     * GET /api/my-parcels
     */
    public function myParcels(Request $request)
    {
        $user = $request->user();

        if (!$user->isRider()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $rider = $user->rider;

        $parcels = Parcel::with(['status', 'sourceHub'])
            ->where('assigned_rider_id', $rider->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $parcels
        ]);
    }

    /**
     * Get my earnings
     * GET /api/my-earnings
     */
    public function myEarnings(Request $request)
    {
        $user = $request->user();

        if (!$user->isRider()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $rider = $user->rider;

        $totalEarnings = Parcel::where('assigned_rider_id', $rider->id)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->sum(DB::raw('delivery_charge * 0.7'));

        $totalDeliveries = Parcel::where('assigned_rider_id', $rider->id)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->count();

        $monthlyEarnings = Parcel::where('assigned_rider_id', $rider->id)
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->whereYear('delivered_at', date('Y'))
            ->select(DB::raw('MONTH(delivered_at) as month'), DB::raw('SUM(delivery_charge * 0.7) as earnings'))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_earnings' => $totalEarnings,
                'total_deliveries' => $totalDeliveries,
                'monthly_earnings' => $monthlyEarnings,
                'current_status' => $rider->status,
            ]
        ]);
    }

    /**
     * Update rider status
     * PATCH /api/rider/status
     */
    public function updateStatus(Request $request)
    {
        $user = $request->user();

        if (!$user->isRider()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:available,busy,offline',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $rider = $user->rider;
        $rider->status = $request->status;
        $rider->save();

        return response()->json([
            'success' => true,
            'message' => 'Rider status updated successfully',
            'data' => ['status' => $rider->status]
        ]);
    }
}
