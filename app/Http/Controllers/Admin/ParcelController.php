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

class ParcelController extends Controller
{
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
            ->select('parcels.*');

        $csrf = csrf_token();

        return DataTables::eloquent($parcels)
            ->editColumn('weight', fn($parcel) => $parcel->weight . ' kg')
            ->editColumn('created_at', fn($parcel) => $parcel->created_at->format('d M Y'))
            ->addColumn('status_badge', function($parcel) {
                $color = $parcel->status->color_code ?? '#6c757d';
                return '<span class="badge" style="background-color: ' . $color . '; color: white;">'
                    . ($parcel->status->display_name ?? 'Unknown') . '</span>';
            })
            ->addColumn('rider_name', fn($parcel) => $parcel->assignedRider->user->name ?? 'Unassigned')
            ->addColumn('action', function($parcel) use ($csrf) {
                return '
                    <a href="' . route('admin.parcels.show', $parcel->id) . '" class="btn btn-sm btn-info">View</a>
                    <a href="' . route('admin.parcels.edit', $parcel->id) . '" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="' . route('admin.parcels.destroy', $parcel->id) . '" style="display:inline;">
                        <input type="hidden" name="_token" value="' . $csrf . '">
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</button>
                    </form>
                ';
            })
            ->rawColumns(['status_badge', 'action'])
            ->toJson();
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

        // AUTO-ASSIGN: If no rider manually selected, try to auto-assign
        if (empty($validated['assigned_rider_id'])) {
            $bestRider = Rider::findBestRiderForParcel(
                $validated['weight'],
                $validated['size'],
                $validated['source_hub_id']
            );

            if ($bestRider) {
                $validated['assigned_rider_id'] = $bestRider->id;
                $validated['status_id'] = ParcelStatus::where('slug', 'assigned')->first()->id;
                $validated['assigned_at'] = now();

                // Update rider status to busy
                $bestRider->status = 'busy';
                $bestRider->save();

                // Send notification to rider
                $this->sendNotificationToRider($bestRider->id, $validated['tracking_number']);

                $autoAssignedMessage = " Auto-assigned to rider: " . $bestRider->user->name;
            } else {
                $validated['status_id'] = ParcelStatus::where('slug', 'pending')->first()->id;
                $autoAssignedMessage = " No rider available. Parcel is pending assignment.";
            }
        } else {
            // Manual assignment
            $validated['status_id'] = ParcelStatus::where('slug', 'assigned')->first()->id;
            $validated['assigned_at'] = now();

            // Update rider status
            $rider = Rider::find($validated['assigned_rider_id']);
            if ($rider) {
                $rider->status = 'busy';
                $rider->save();
                $this->sendNotificationToRider($rider->id, $validated['tracking_number']);
            }
            $autoAssignedMessage = "";
        }

        $parcel = Parcel::create($validated);

        return redirect()->route('admin.parcels.index')
            ->with('success', 'Parcel created successfully. Tracking #: ' . $parcel->tracking_number . $autoAssignedMessage);
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
     * Find the best rider for a parcel (API endpoint)
     */
    public function findBestRider(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:0.1',
            'size' => 'required|numeric|min:0.1',
            'hub_id' => 'nullable|exists:hubs,id',
        ]);

        $bestRider = Rider::findBestRiderForParcel(
            $request->weight,
            $request->size,
            $request->hub_id
        );

        if ($bestRider) {
            return response()->json([
                'success' => true,
                'rider' => [
                    'id' => $bestRider->id,
                    'name' => $bestRider->user->name,
                    'employee_id' => $bestRider->employee_id,
                    'max_weight_capacity' => $bestRider->max_weight_capacity,
                    'max_size_capacity' => $bestRider->max_size_capacity,
                    'status' => $bestRider->status,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No available rider found'
        ]);
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
     * Add this method to your existing ParcelController
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
}
