<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackingController extends Controller
{
    public function track(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tracking_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $parcel = Parcel::with(['status', 'assignedRider.user'])
            ->where('tracking_number', $request->tracking_number)
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
                'status' => $parcel->status->display_name ?? 'Unknown',
                'sender' => $parcel->sender_name,
                'receiver' => $parcel->receiver_name,
            ]
        ]);
    }
}
