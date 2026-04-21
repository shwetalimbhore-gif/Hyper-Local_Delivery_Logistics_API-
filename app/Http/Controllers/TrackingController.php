<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\ParcelStatusHistory;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Show tracking form
     */
    public function index()
    {
        return view('tracking.index');
    }

    /**
     * Track parcel by tracking number
     */
    public function track(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string|exists:parcels,tracking_number',
        ], [
            'tracking_number.exists' => 'Invalid tracking number. Please check and try again.',
        ]);

        $parcel = Parcel::where('tracking_number', $request->tracking_number)
            ->with(['status', 'sourceHub', 'assignedRider.user', 'statusHistories' => function($q) {
                $q->with(['status', 'updater'])->orderBy('created_at', 'desc');
            }])
            ->first();

        if (!$parcel) {
            return back()->with('error', 'Parcel not found!');
        }

        return view('tracking.show', compact('parcel'));
    }

    /**
     * Get parcel status via AJAX (for real-time tracking)
     */
    public function getStatus(Request $request)
    {
        $request->validate([
            'tracking_number' => 'required|string|exists:parcels,tracking_number',
        ]);

        $parcel = Parcel::where('tracking_number', $request->tracking_number)
            ->with(['status', 'assignedRider.user'])
            ->first();

        if (!$parcel) {
            return response()->json([
                'success' => false,
                'message' => 'Parcel not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tracking_number' => $parcel->tracking_number,
                'status' => [
                    'name' => $parcel->status->display_name,
                    'slug' => $parcel->status->slug,
                    'color' => $parcel->status->color_code,
                ],
                'sender_name' => $parcel->sender_name,
                'receiver_name' => $parcel->receiver_name,
                'estimated_delivery' => $this->calculateEstimatedDelivery($parcel),
                'current_location' => $this->getCurrentLocation($parcel),
                'last_updated' => $parcel->updated_at->format('d M Y, h:i A'),
            ]
        ]);
    }

    /**
     * Calculate estimated delivery date
     */
    private function calculateEstimatedDelivery($parcel)
    {
        if ($parcel->status->slug === 'delivered') {
            return $parcel->delivered_at->format('d M Y, h:i A');
        }

        // Estimated delivery is 2-3 days from creation
        return $parcel->created_at->addDays(3)->format('d M Y');
    }

    /**
     * Get current location based on status
     */
    private function getCurrentLocation($parcel)
    {
        switch ($parcel->status->slug) {
            case 'pending':
                return $parcel->sourceHub->name ?? 'Hub';
            case 'assigned':
                return 'Waiting for rider pickup';
            case 'picked-up':
                return 'Picked up from ' . ($parcel->sourceHub->name ?? 'Hub');
            case 'out-for-delivery':
                return 'Out for delivery';
            case 'delivered':
                return 'Delivered to ' . $parcel->receiver_name;
            case 'failed-delivery':
                return 'Delivery failed - ' . ($parcel->failure_reason ?? 'Contact support');
            default:
                return 'In transit';
        }
    }
}
