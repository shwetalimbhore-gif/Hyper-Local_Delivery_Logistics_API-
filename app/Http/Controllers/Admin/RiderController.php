<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use App\Models\Hub;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\RiderStoreRequest;
use App\Http\Requests\Admin\RiderUpdateRequest;


class RiderController extends Controller
{
    /**
     * Display the riders index page
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
        $riders = Rider::with(['user', 'hub'])
            ->select('riders.*');

        $csrf = csrf_token();

        return DataTables::eloquent($riders)
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
                    'bike' => 'primary',
                    'scooter' => 'info',
                    'bicycle' => 'success',
                    'car' => 'warning',
                    'truck' => 'danger'
                ];
                $color = $colors[$rider->vehicle_type] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($rider->vehicle_type) . '</span>';
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
                return number_format($rider->rating, 1) . ' <iconify-icon icon="solar:star-bold" class="text-warning"></iconify-icon>';
            })
            ->addColumn('action', function($rider) use ($csrf) {
                return '
                    <div class="btn-group" role="group">
                        <a href="' . route('admin.riders.show', $rider->id) . '" class="btn btn-sm btn-info" title="View">
                            <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                        </a>
                        <a href="' . route('admin.riders.edit', $rider->id) . '" class="btn btn-sm btn-warning" title="Edit">
                            <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                        </a>
                        <form method="POST" action="' . route('admin.riders.destroy', $rider->id) . '" style="display:inline;" onsubmit="return confirm(\'Are you sure?\')">
                            <input type="hidden" name="_token" value="' . $csrf . '">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['vehicle_badge', 'status_badge', 'rating_display', 'action'])
            ->make(true);
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
     * Store a newly created rider (Using FormRequest)
     */
    public function store(RiderStoreRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
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
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'vehicle_model' => $validated['vehicle_model'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
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

    /**
     * Display the specified rider.
     */
    public function show($id)
    {
        $rider = Rider::with(['user', 'hub', 'assignedParcels' => function($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);

        return view('admin.riders.show', compact('rider'));
    }

    /**
     * Show the form for editing the specified rider.
     */
    public function edit($id)
    {
        $rider = Rider::findOrFail($id);
        $hubs = Hub::where('is_active', true)->get();
        return view('admin.riders.edit', compact('rider', 'hubs'));
    }

   /**
     * Update the specified rider (Using FormRequest)
     */
    public function update(RiderUpdateRequest $request, $id)
    {
        $rider = Rider::findOrFail($id);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $rider->user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
            ]);

            $rider->update([
                'hub_id' => $validated['hub_id'],
                'vehicle_type' => $validated['vehicle_type'],
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'vehicle_model' => $validated['vehicle_model'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
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
            $rider->delete();

            if ($rider->user) {
                $rider->user->delete();
            }

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider moved to trash successfully');

        } catch (\Exception $e) {
            return redirect()->route('admin.riders.index')
                ->with('error', 'Failed to delete rider: ' . $e->getMessage());
        }
    }

    /**
     * Display trashed riders.
     */
    public function trash()
    {
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

            if ($rider->user) {
                $rider->user->restore();
            }

            return redirect()->route('admin.riders.trash')
                ->with('success', 'Rider restored successfully');

        } catch (\Exception $e) {
            return redirect()->route('admin.riders.trash')
                ->with('error', 'Failed to restore rider');
        }
    }

    /**
     * Permanently delete a soft deleted rider.
     */
    public function forceDelete($id)
    {
        try {
            $rider = Rider::withTrashed()->findOrFail($id);
            $rider->forceDelete();

            if ($rider->user) {
                $rider->user->forceDelete();
            }

            return redirect()->route('admin.riders.trash')
                ->with('success', 'Rider permanently deleted');

        } catch (\Exception $e) {
            return redirect()->route('admin.riders.trash')
                ->with('error', 'Failed to permanently delete rider');
        }
    }
}
