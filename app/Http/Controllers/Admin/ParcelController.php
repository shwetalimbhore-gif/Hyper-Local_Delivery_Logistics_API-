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
                return '<span class="badge" style="background-color: ' . $color . '; color: white;">'
                    . ($parcel->status->display_name ?? 'Unknown') . '</span>';
            })
            ->addColumn('rider_name', function($parcel) {
                return $parcel->assignedRider->user->name ?? 'Unassigned';
            })
            ->addColumn('actions', function($parcel) {
                $csrf = csrf_token();
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
            ->rawColumns(['status_html', 'actions'])
            ->toJson();
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

        return redirect()->route('admin.parcels.index')
            ->with('success', 'Parcel created successfully. Tracking #: ' . $parcel->tracking_number);
    }

    public function edit(Parcel $parcel)
    {
        $hubs = Hub::where('is_active', true)->get();
        $riders = Rider::with('user')->where('status', 'available')->get();
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
        $validated = $request->validated();
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
     * Soft delete parcel (move to trash)
     */
    public function destroy($id)
    {
        try {
            $parcel = Parcel::findOrFail($id);
            $parcel->delete();

            // Return JSON response for AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Parcel moved to trash successfully'
                ]);
            }

            return redirect()->back()->with('success', 'Parcel moved to trash');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error moving parcel to trash');
        }
    }

    /**
     * Restore soft deleted parcel
     */
    public function restore($id)
    {
        try {
            $parcel = Parcel::withTrashed()->findOrFail($id);
            $parcel->restore();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Parcel restored successfully'
                ]);
            }

            return redirect()->back()->with('success', 'Parcel restored successfully');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error restoring parcel');
        }
    }

    /**
     * Force delete parcel permanently
     */
    public function forceDelete($id)
    {
        try {
            $parcel = Parcel::withTrashed()->findOrFail($id);
            $parcel->forceDelete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Parcel permanently deleted'
                ]);
            }

            return redirect()->back()->with('success', 'Parcel permanently deleted');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error deleting parcel');
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

    /**
 * Display the specified parcel.
 */
public function show($id)
{
    $parcel = Parcel::with(['status', 'assignedRider.user', 'sourceHub', 'statusHistories.updater'])
        ->findOrFail($id);

    return view('admin.parcels.show', compact('parcel'));
}
}
