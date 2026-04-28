<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\Hub;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Admin\RiderStoreRequest;
use App\Http\Requests\Admin\RiderUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiderController extends Controller
{
    /**
     * Display a listing of riders.
     */
    public function index()
    {
        return view('admin.riders.index');
    }

    /**
     * Get riders data for DataTable via AJAX
     */
    public function getData(Request $request)
    {
        try {
            $riders = Rider::with(['user', 'hub'])
                ->select('riders.*');

            return DataTables::eloquent($riders)
                ->addColumn('full_name', function($row) {
                    return $row->user ? e($row->user->name) : 'N/A';
                })
                ->addColumn('email', function($row) {
                    return $row->user ? e($row->user->email) : 'N/A';
                })
                ->addColumn('phone', function($row) {
                    return $row->user ? e($row->user->phone) : 'N/A';
                })
                ->addColumn('hub_name', function($row) {
                    return $row->hub ? e($row->hub->name) : '<span class="text-muted">No Hub</span>';
                })
                ->addColumn('vehicle_badge', function($row) {
                    $vehicleIcon = $row->vehicle_type == 'bike' ? 'solar:bicycle-line-duotone' :
                                   ($row->vehicle_type == 'car' ? 'solar:car-line-duotone' : 'solar:truck-line-duotone');
                    return '<span class="badge bg-secondary">
                                <iconify-icon icon="' . $vehicleIcon . '" class="me-1"></iconify-icon>
                                ' . ucfirst($row->vehicle_type) . '
                            </span>';
                })
                ->addColumn('status_badge', function($row) {
                    $statusColors = [
                        'available' => 'success',
                        'busy' => 'warning',
                        'offline' => 'secondary'
                    ];
                    $color = $statusColors[$row->status] ?? 'secondary';
                    $statusIcon = $row->status == 'available' ? 'check-circle-line-duotone' :
                                  ($row->status == 'busy' ? 'clock-circle-line-duotone' : 'power-off-line-duotone');

                    return '<span class="badge bg-' . $color . '">
                                <iconify-icon icon="solar:' . $statusIcon . '" class="me-1"></iconify-icon>
                                ' . ucfirst($row->status) . '
                            </span>';
                })
                ->addColumn('rating_display', function($row) {
                    $rating = $row->rating ?? 0;
                    $fullStars = floor($rating);
                    $halfStar = ($rating - $fullStars) >= 0.5;
                    $emptyStars = 5 - ceil($rating);

                    $stars = '';
                    for ($i = 0; $i < $fullStars; $i++) {
                        $stars .= '<iconify-icon icon="solar:star-bold" class="text-warning"></iconify-icon>';
                    }
                    if ($halfStar) {
                        $stars .= '<iconify-icon icon="solar:star-half-bold" class="text-warning"></iconify-icon>';
                    }
                    for ($i = 0; $i < $emptyStars; $i++) {
                        $stars .= '<iconify-icon icon="solar:star-line-duotone" class="text-muted"></iconify-icon>';
                    }

                    return '<div class="d-flex align-items-center gap-1">
                                ' . $stars . '
                                <small class="text-muted ms-1">(' . number_format($rating, 1) . ')</small>
                            </div>';
                })
                ->addColumn('action', function($row) {
                    return '
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="' . route('admin.riders.show', $row->id) . '" class="btn btn-info btn-sm" title="View">
                                <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                            </a>
                            <a href="' . route('admin.riders.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Edit">
                                <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                            </a>
                            <button type="button" class="btn btn-danger btn-sm soft-delete-btn"
                                    data-id="' . $row->id . '"
                                    data-name="' . e($row->user->name ?? 'Unknown') . '"
                                    data-employee-id="' . e($row->employee_id) . '"
                                    title="Move to Trash">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['hub_name', 'vehicle_badge', 'status_badge', 'rating_display', 'action'])
                ->toJson();

        } catch (\Exception $e) {
            Log::error('Rider DataTable Error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Failed to load riders data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new rider.
     */
    public function create()
    {
        $hubs = Hub::where('is_active', true)->get();
        return view('admin.riders.create', compact('hubs'));
    }

    /**
     * Store a newly created rider.
     */
    public function store(RiderStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            // Check if user with this email already exists
            $existingUser = User::where('email', $request->email)->first();

            if ($existingUser) {
                return back()->with('error', 'A user with this email already exists.')->withInput();
            }

            // Create user account
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'role_id' => 3, // Rider role ID
            ]);

            // Create rider profile
            $rider = Rider::create([
                'user_id' => $user->id,
                'hub_id' => $request->hub_id,
                'employee_id' => $request->employee_id,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'vehicle_model' => $request->vehicle_model,
                'license_number' => $request->license_number,
                'max_weight_capacity' => $request->max_weight_capacity,
                'max_size_capacity' => $request->max_size_capacity,
                'status' => 'available',
                'is_verified' => $request->has('is_verified'),
                'joined_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider created successfully! Employee ID: ' . $rider->employee_id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create rider: ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Display the specified rider.
     */
    public function show(Rider $rider)
    {
        // Load relationships
        $rider->load(['user', 'hub', 'assignedParcels' => function($q) {
            $q->latest()->limit(10);
        }]);

        // Check if user exists
        if (!$rider->user) {
            return redirect()->route('admin.riders.index')
                ->with('error', 'Rider user account not found.');
        }

        $totalEarnings = $rider->assignedParcels()
            ->whereHas('status', function($q) {
                $q->where('slug', 'delivered');
            })
            ->sum('delivery_charge');

        return view('admin.riders.show', compact('rider', 'totalEarnings'));
    }
    /**
     * Show the form for editing the specified rider.
     */
    public function edit(Rider $rider)
    {
        // Load the user relationship
        $rider->load('user');

        // Check if user exists
        if (!$rider->user) {
            return redirect()->route('admin.riders.index')
                ->with('error', 'Rider user account not found. Please contact support.');
        }

        $hubs = Hub::where('is_active', true)->get();
        return view('admin.riders.edit', compact('rider', 'hubs'));
    }

    /**
     * Update the specified rider.
     */
    public function update(RiderUpdateRequest $request, Rider $rider)
    {
        DB::beginTransaction();

        try {
            // Update user
            $userData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
            ];

            if ($request->filled('email') && $request->email != $rider->user->email) {
                $userData['email'] = $request->email;
            }

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $rider->user->update($userData);

            // Update rider
            $rider->update([
                'hub_id' => $request->hub_id,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'vehicle_model' => $request->vehicle_model,
                'license_number' => $request->license_number,
                'max_weight_capacity' => $request->max_weight_capacity,
                'max_size_capacity' => $request->max_size_capacity,
                'is_verified' => $request->has('is_verified'),
            ]);

            DB::commit();

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update rider: ' . $e->getMessage());
        }
    }

   /**
     * Soft delete the specified rider.
     */
    public function destroy($id)
    {
        try {
            $rider = Rider::findOrFail($id);

            // ✅ CHECK IF RIDER IS BUSY
            if ($rider->status === 'busy') {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete rider because they are currently BUSY with active deliveries. Please wait until deliveries are completed.'
                    ], 400);
                }

                return redirect()->route('admin.riders.show', $rider->id)
                    ->with('error', 'Cannot delete rider because they are currently BUSY with active deliveries.');
            }

            // Check if rider has active parcels (additional safety)
            $activeParcels = $rider->assignedParcels()
                ->whereHas('status', function($q) {
                    $q->whereNotIn('slug', ['delivered', 'cancelled', 'returned_to_sender']);
                })->count();

            if ($activeParcels > 0) {
                $message = "Cannot delete rider because they have {$activeParcels} active parcel(s). Please reassign or complete the deliveries first.";

                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }

                return redirect()->route('admin.riders.show', $rider->id)
                    ->with('error', $message);
            }

            // Soft delete the rider
            $rider->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rider moved to trash successfully.'
                ]);
            }

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider moved to trash successfully.');

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete rider: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.riders.show', $id)
                ->with('error', 'Failed to delete rider: ' . $e->getMessage());
        }
    }
    /**
     * Display trashed riders.
     */
    public function trash()
    {
        // Option 1: If using pagination (without DataTable)
        $riders = Rider::onlyTrashed()
            ->with(['user', 'hub'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('admin.riders.trash', compact('riders'));
    }

    /**
     * Get trashed riders data for DataTable.
     */
    public function getTrashData(Request $request)
    {
        try {
            $riders = Rider::onlyTrashed()
                ->with(['user', 'hub'])
                ->select('riders.*');

            return DataTables::eloquent($riders)
                ->addColumn('checkbox', function($row) {
                    return '<input type="checkbox" class="rider-checkbox" value="' . $row->id . '">';
                })
                ->addColumn('full_name', function($row) {
                    return $row->user ? e($row->user->name) : 'N/A';
                })
                ->addColumn('employee_id', function($row) {
                    return e($row->employee_id);
                })
                ->addColumn('hub_name', function($row) {
                    return $row->hub ? e($row->hub->name) : 'N/A';
                })
                ->addColumn('deleted_at', function($row) {
                    return $row->deleted_at->format('d M Y, h:i A');
                })
                ->addColumn('actions', function($row) {
                    return '
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-success btn-sm restore-btn"
                                    data-id="' . $row->id . '"
                                    data-name="' . e($row->user->name ?? 'Unknown') . '">
                                <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                                Restore
                            </button>
                            <button type="button" class="btn btn-danger btn-sm force-delete-btn"
                                    data-id="' . $row->id . '"
                                    data-name="' . e($row->user->name ?? 'Unknown') . '">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                                Delete Forever
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['checkbox', 'actions'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Trash DataTable Error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Failed to load trash data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted rider.
     */
    public function restore($id)
    {
        try {
            $rider = Rider::onlyTrashed()->findOrFail($id);
            $rider->restore();

            // Also restore the user if it was soft deleted
            // if ($rider->user) {
            //     $rider->user->restore();
            // }

            return response()->json([
                'success' => true,
                'message' => 'Rider restored successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore rider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete a soft deleted rider.
     */
    public function forceDelete($id)
    {
        try {
            $rider = Rider::onlyTrashed()->findOrFail($id);

            // Store user id before deleting rider
            $userId = $rider->user_id;

            // Force delete rider
            $rider->forceDelete();

            // Optionally force delete the associated user
            // User::where('id', $userId)->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Rider permanently deleted.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete rider: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk restore multiple riders.
     */
    public function bulkRestore(Request $request)
    {
        try {
            $ids = $request->ids;
            Rider::onlyTrashed()->whereIn('id', $ids)->restore();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' riders restored successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore riders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk force delete multiple riders.
     */
    public function bulkForceDelete(Request $request)
    {
        try {
            $ids = $request->ids;
            Rider::onlyTrashed()->whereIn('id', $ids)->forceDelete();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' riders permanently deleted.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete riders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle rider verification status.
     */
    public function toggleVerification(Rider $rider)
    {
        try {
            $rider->is_verified = !$rider->is_verified;
            $rider->save();

            $status = $rider->is_verified ? 'verified' : 'unverified';

            return response()->json([
                'success' => true,
                'message' => "Rider {$status} successfully!",
                'is_verified' => $rider->is_verified
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update rider status via AJAX
     */
    public function updateStatus(Request $request, Rider $rider)
    {
        try {
            $request->validate([
                'status' => 'required|in:available,busy,offline'
            ]);

            $oldStatus = $rider->status;
            $rider->status = $request->status;
            $rider->save();

            // Send notification to rider about status change
            if ($rider->user) {
                Notification::create([
                    'user_id' => $rider->user_id,
                    'title' => 'Status Updated',
                    'message' => "Your status has been changed from " . ucfirst($oldStatus) . " to " . ucfirst($request->status),
                    'type' => 'info',
                    'is_read' => false,
                ]);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rider status updated successfully',
                    'status' => $rider->status
                ]);
            }

            return back()->with('success', 'Rider status updated successfully');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update status: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }
}
