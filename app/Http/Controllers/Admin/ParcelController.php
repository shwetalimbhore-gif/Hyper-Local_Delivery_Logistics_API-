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

class ParcelController extends Controller
{
    public function index()
    {
        $parcels = Parcel::with(['status', 'assignedRider.user'])
            ->latest()
            ->paginate(15);

        return view('admin.parcels.index', compact('parcels'));
    }

    public function create()
    {
        $hubs = Hub::where('is_active', true)->get();
        return view('admin.parcels.create', compact('hubs'));
    }

   /**
 * Store a newly created parcel in storage.
 */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:20',
            'sender_email' => 'nullable|email',
            'sender_address' => 'required|string',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'receiver_email' => 'nullable|email',
            'receiver_address' => 'required|string',
            'parcel_name' => 'required|string|max:255',
            'parcel_description' => 'nullable|string',
            'weight' => 'required|numeric|min:0.1',
            'size' => 'required|numeric|min:0.1',
            'parcel_type' => 'nullable|string',
            'delivery_charge' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'source_hub_id' => 'required|exists:hubs,id',
            'assigned_rider_id' => 'nullable|exists:riders,id',
            'notes' => 'nullable|string',
        ]);

        // Generate tracking number
        $validated['tracking_number'] = $this->generateTrackingNumber();
        $validated['created_by'] = Auth::id();

        // Check if rider is assigned
        if (!empty($validated['assigned_rider_id'])) {
            $validated['status_id'] = ParcelStatus::where('slug', 'assigned')->first()->id;
            $validated['assigned_at'] = now();

            // Update rider status to busy
            $rider = Rider::find($validated['assigned_rider_id']);
            if ($rider) {
                $rider->status = 'busy';
                $rider->save();
            }
        } else {
            $validated['status_id'] = ParcelStatus::where('slug', 'pending')->first()->id;
        }

        $parcel = Parcel::create($validated);

        // Send notification to rider if assigned
        if (!empty($validated['assigned_rider_id'])) {
            $this->sendNotificationToRider($validated['assigned_rider_id'], $parcel);
        }

        return redirect()->route('admin.parcels.index')
            ->with('success', 'Parcel created successfully. Tracking #: ' . $parcel->tracking_number);
    }
    public function show(Parcel $parcel)
    {
        $parcel->load(['status', 'assignedRider.user', 'sourceHub', 'statusHistories.updater']);
        return view('admin.parcels.show', compact('parcel'));
    }

    public function edit(Parcel $parcel)
    {
        $hubs = Hub::where('is_active', true)->get();
        $riders = Rider::with('user')->where('status', 'available')->get();
        $statuses = ParcelStatus::all();

        return view('admin.parcels.edit', compact('parcel', 'hubs', 'riders', 'statuses'));
    }

    /**
     * Update the specified parcel in storage.
     */
    public function update(Request $request, Parcel $parcel)
    {
        $validated = $request->validate([
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
            'status_id' => 'required|exists:parcel_statuses,id',
            'notes' => 'nullable|string',
        ]);

        $oldRiderId = $parcel->assigned_rider_id;
        $newRiderId = $validated['assigned_rider_id'] ?? null;

        // Handle rider assignment status change
        if ($newRiderId && $oldRiderId != $newRiderId) {
            // New rider assigned
            $validated['status_id'] = ParcelStatus::where('slug', 'assigned')->first()->id;
            $validated['assigned_at'] = now();

            // Update old rider status back to available if they have no other active parcels
            if ($oldRiderId) {
                $oldRider = Rider::find($oldRiderId);
                if ($oldRider) {
                    $activeParcels = Parcel::where('assigned_rider_id', $oldRiderId)
                        ->whereHas('status', function($q) {
                            $q->whereNotIn('slug', ['delivered', 'cancelled', 'returned_to_sender']);
                        })->count();

                    if ($activeParcels == 0) {
                        $oldRider->status = 'available';
                        $oldRider->save();
                    }
                }
            }

            // Update new rider status to busy
            $newRider = Rider::find($newRiderId);
            if ($newRider) {
                $newRider->status = 'busy';
                $newRider->save();
            }

            // Send notification to new rider
            $this->sendNotificationToRider($newRiderId, $parcel);

        } elseif (!$newRiderId && $oldRiderId) {
            // Rider removed (unassigned)
            $validated['status_id'] = ParcelStatus::where('slug', 'pending')->first()->id;
            $validated['assigned_at'] = null;

            // Update rider status
            $oldRider = Rider::find($oldRiderId);
            if ($oldRider) {
                $activeParcels = Parcel::where('assigned_rider_id', $oldRiderId)
                    ->whereHas('status', function($q) {
                        $q->whereNotIn('slug', ['delivered', 'cancelled', 'returned_to_sender']);
                    })->count();

                if ($activeParcels == 0) {
                    $oldRider->status = 'available';
                    $oldRider->save();
                }
            }
        }

        $parcel->update($validated);

        return redirect()->route('admin.parcels.index')
            ->with('success', 'Parcel updated successfully');
    }

    /**
     * Display trashed parcels (soft deleted)
     */
    public function trash()
    {
        $parcels = Parcel::onlyTrashed()
            ->with(['status', 'assignedRider.user'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('admin.parcels.trash', compact('parcels'));
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
     * Restore a soft deleted parcel.
     */
    public function restore($id)
    {
        try {
            $parcel = Parcel::withTrashed()->findOrFail($id);
            $parcel->restore();

            return redirect()->route('admin.parcels.trash')
                ->with('success', 'Parcel restored successfully.');

        } catch (\Exception $e) {
            return redirect()->route('admin.parcels.trash')
                ->with('error', 'Failed to restore parcel: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a soft deleted parcel.
     */
    public function forceDelete($id)
    {
        try {
            $parcel = Parcel::withTrashed()->findOrFail($id);
            $parcel->forceDelete();

            return redirect()->route('admin.parcels.trash')
                ->with('success', 'Parcel permanently deleted.');

        } catch (\Exception $e) {
            return redirect()->route('admin.parcels.trash')
                ->with('error', 'Failed to permanently delete parcel: ' . $e->getMessage());
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
     * Send notification to rider when assigned.
     */
    private function sendNotificationToRider($riderId, $parcel)
    {
        $rider = Rider::with('user')->find($riderId);

        if ($rider && $rider->user) {
            Notification::create([
                'user_id' => $rider->user->id,
                'title' => 'New Parcel Assigned',
                'message' => "Parcel #{$parcel->tracking_number} has been assigned to you. Please check your dashboard.",
                'type' => 'info',
                'data' => ['parcel_id' => $parcel->id, 'tracking_number' => $parcel->tracking_number],
                'is_read' => false,
            ]);
        }
    }
}
