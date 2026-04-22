<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use App\Models\Hub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RiderController extends Controller
{
    public function index()
    {
        $riders = Rider::with(['user', 'hub'])->latest()->paginate(15);
        return view('admin.riders.index', compact('riders'));
    }

    public function create()
    {
        $hubs = Hub::where('is_active', true)->get();
        return view('admin.riders.create', compact('hubs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'password' => 'required|string|min:8',
            'hub_id' => 'required|exists:hubs,id',
            'employee_id' => 'required|string|unique:riders,employee_id',
            'vehicle_type' => 'required|in:bike,scooter,bicycle,car,truck',
            'vehicle_number' => 'nullable|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:50',
            'max_weight_capacity' => 'nullable|numeric|min:0',
            'max_size_capacity' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'role_id' => 2,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
                'is_active' => true,
            ]);

            Rider::create([
                'user_id' => $user->id,
                'hub_id' => $validated['hub_id'],
                'employee_id' => $validated['employee_id'],
                'vehicle_type' => $validated['vehicle_type'],
                'vehicle_number' => $validated['vehicle_number'],
                'vehicle_model' => $validated['vehicle_model'],
                'license_number' => $validated['license_number'],
                'max_weight_capacity' => $validated['max_weight_capacity'] ?? 50,
                'max_size_capacity' => $validated['max_size_capacity'] ?? 100,
                'status' => 'available',
                'is_verified' => true,
                'joined_date' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider created successfully! Password: ' . $validated['password']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create rider: ' . $e->getMessage()]);
        }
    }

    public function show($id)  // Changed from show(Rider $rider)
    {
        $rider = Rider::with(['user', 'hub', 'assignedParcels' => function($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);

        return view('admin.riders.show', compact('rider'));
    }

    public function edit($id)  // Changed from edit(Rider $rider)
    {
        $rider = Rider::findOrFail($id);
        $hubs = Hub::where('is_active', true)->get();
        return view('admin.riders.edit', compact('rider', 'hubs'));
    }

    public function update(Request $request, $id)  // Changed to $id
    {
        $rider = Rider::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'hub_id' => 'required|exists:hubs,id',
            'vehicle_type' => 'required|in:bike,scooter,bicycle,car,truck',
            'vehicle_number' => 'nullable|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:50',
            'max_weight_capacity' => 'nullable|numeric|min:0',
            'max_size_capacity' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,busy,offline',
        ]);

        try {
            DB::beginTransaction();

            $rider->user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
            ]);

            $rider->update([
                'hub_id' => $validated['hub_id'],
                'vehicle_type' => $validated['vehicle_type'],
                'vehicle_number' => $validated['vehicle_number'],
                'vehicle_model' => $validated['vehicle_model'],
                'license_number' => $validated['license_number'],
                'max_weight_capacity' => $validated['max_weight_capacity'],
                'max_size_capacity' => $validated['max_size_capacity'],
                'status' => $validated['status'],
            ]);

            DB::commit();

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update rider: ' . $e->getMessage()]);
        }
    }

       /**
     * Remove the specified rider from storage (soft delete).
     */
    public function destroy($id)
    {
        try {
            $rider = Rider::findOrFail($id);

            // Check if rider has active parcels
            $activeParcels = $rider->assignedParcels()
                ->whereHas('status', function($q) {
                    $q->whereNotIn('slug', ['delivered', 'cancelled']);
                })->count();

            if ($activeParcels > 0) {
                return redirect()->route('admin.riders.index')
                    ->with('error', 'Cannot delete rider with active deliveries. Please reassign their parcels first.');
            }

            // SOFT DELETE - This sets deleted_at timestamp
            $rider->delete();

            // Also soft delete the associated user
            if ($rider->user) {
                $rider->user->delete();
            }

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider moved to trash successfully.');

        } catch (\Exception $e) {
            return redirect()->route('admin.riders.index')
                ->with('error', 'Failed to delete rider: ' . $e->getMessage());
        }
    }

    /**
     * Display trashed riders (soft deleted).
     */
    public function trash()
    {
        // ONLY get soft deleted records
        $riders = Rider::onlyTrashed()
            ->with(['user', 'hub'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('admin.riders.trash', compact('riders'));
    }

   /**
     * Restore a soft deleted rider.
     */
    public function restore($id)
    {
        try {
            $rider = Rider::withTrashed()->findOrFail($id);
            $rider->restore();

            // Also restore the associated user
            if ($rider->user) {
                $rider->user->restore();
            }

            return redirect()->route('admin.riders.trash')
                ->with('success', 'Rider restored successfully.');

        } catch (\Exception $e) {
            return redirect()->route('admin.riders.trash')
                ->with('error', 'Failed to restore rider: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a soft deleted rider.
     */
    public function forceDelete($id)
    {
        try {
            $rider = Rider::withTrashed()->findOrFail($id);

            // Check if rider has any parcels
            if ($rider->assignedParcels()->count() > 0) {
                return redirect()->route('admin.riders.trash')
                    ->with('error', 'Cannot permanently delete rider who has delivery history.');
            }

            // Permanently delete the rider
            $rider->forceDelete();

            // Permanently delete the associated user
            if ($rider->user) {
                $rider->user->forceDelete();
            }

            return redirect()->route('admin.riders.trash')
                ->with('success', 'Rider permanently deleted.');

        } catch (\Exception $e) {
            return redirect()->route('admin.riders.trash')
                ->with('error', 'Failed to permanently delete rider: ' . $e->getMessage());
        }
    }

    /**
     * Riders DataTable - Server Side
     */
    public function getDataTable(Request $request)
    {
        if ($request->ajax()) {
            $riders = Rider::with(['user', 'hub'])
                ->select('riders.*');

            return DataTables::of($riders)
                ->addColumn('full_name', function($rider) {
                    return $rider->user->name ?? 'N/A';
                })
                ->addColumn('email', function($rider) {
                    return $rider->user->email ?? 'N/A';
                })
                ->addColumn('phone', function($rider) {
                    return $rider->user->phone ?? 'N/A';
                })
                ->addColumn('hub_name', function($rider) {
                    return $rider->hub->name ?? 'N/A';
                })
                ->addColumn('vehicle_badge', function($rider) {
                    $colors = [
                        'bike' => 'bg-primary',
                        'scooter' => 'bg-info',
                        'bicycle' => 'bg-success',
                        'car' => 'bg-warning',
                        'truck' => 'bg-danger'
                    ];
                    $color = $colors[$rider->vehicle_type] ?? 'bg-secondary';
                    return '<span class="badge ' . $color . '">' . ucfirst($rider->vehicle_type) . '</span>';
                })
                ->addColumn('status_badge', function($rider) {
                    if ($rider->status == 'available') {
                        return '<span class="badge bg-success">Available</span>';
                    } elseif ($rider->status == 'busy') {
                        return '<span class="badge bg-warning">Busy</span>';
                    } else {
                        return '<span class="badge bg-secondary">Offline</span>';
                    }
                })
                ->addColumn('rating_display', function($rider) {
                    return '<div class="d-flex align-items-center">
                                <span class="me-1">' . number_format($rider->rating, 1) . '</span>
                                <iconify-icon icon="solar:star-bold" class="text-warning"></iconify-icon>
                            </div>';
                })
                ->addColumn('action', function($rider) {
                    return '
                        <div class="btn-group" role="group">
                            <a href="' . route('admin.riders.show', $rider->id) . '" class="btn btn-sm btn-info" title="View">
                                <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                            </a>
                            <a href="' . route('admin.riders.edit', $rider->id) . '" class="btn btn-sm btn-warning" title="Edit">
                                <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="confirmDeleteRider(' . $rider->id . ')">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                            </button>
                        </div>
                    ';
                })
                ->editColumn('total_deliveries', function($rider) {
                    return '<span class="fw-bold">' . $rider->total_deliveries . '</span>';
                })
                ->rawColumns(['vehicle_badge', 'status_badge', 'rating_display', 'action', 'total_deliveries'])
                ->make(true);
        }

        return view('admin.riders.datatable');
    }
}
