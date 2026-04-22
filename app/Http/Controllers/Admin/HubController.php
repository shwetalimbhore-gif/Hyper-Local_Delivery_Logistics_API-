<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hub;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class HubController extends Controller
{
    /**
     * Display a listing of hubs.
     */
    public function index()
    {
        $hubs = Hub::latest()->paginate(15);
        return view('admin.hubs.index', compact('hubs'));
    }

    /**
     * Show the form for creating a new hub.
     */
    public function create()
    {
        return view('admin.hubs.create');
    }

    /**
     * Store a newly created hub in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:hubs,code',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $validated['is_active'] = $request->has('is_active') ? true : false;

            $hub = Hub::create($validated);

            return redirect()->route('admin.hubs.index')
                ->with('success', 'Hub created successfully! Hub Code: ' . $hub->code);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create hub: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified hub.
     */
    public function show(Hub $hub)
    {
        $hub->load(['riders' => function($q) {
            $q->with('user')->limit(10);
        }, 'sourceParcels' => function($q) {
            $q->latest()->limit(10);
        }]);

        $riderCount = $hub->riders()->count();
        $parcelCount = $hub->sourceParcels()->count();

        return view('admin.hubs.show', compact('hub', 'riderCount', 'parcelCount'));
    }

    /**
     * Show the form for editing the specified hub.
     */
    public function edit(Hub $hub)
    {
        return view('admin.hubs.edit', compact('hub'));
    }

    /**
     * Update the specified hub in storage.
     */
    public function update(Request $request, Hub $hub)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:hubs,code,' . $hub->id,
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            $validated['is_active'] = $request->has('is_active') && $request->input('is_active') == 1;

            $hub->update($validated);

            return redirect()->route('admin.hubs.index')
                ->with('success', 'Hub updated successfully! Status is now ' . ($hub->is_active ? 'ACTIVE' : 'INACTIVE'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update hub: ' . $e->getMessage()]);
        }
    }

    /**
     * SOFT DELETE - Remove the specified hub from storage.
     */
    public function destroy($id)
    {
        try {
            $hub = Hub::findOrFail($id);

            // Check if hub has any riders
            if ($hub->riders()->count() > 0) {
                return redirect()->route('admin.hubs.index')
                    ->with('error', 'Cannot delete hub because it has assigned riders. Please reassign or delete the riders first.');
            }

            // Check if hub has any parcels
            if ($hub->sourceParcels()->count() > 0) {
                return redirect()->route('admin.hubs.index')
                    ->with('error', 'Cannot delete hub because it has associated parcels. Please reassign or delete the parcels first.');
            }

            // Soft delete the hub
            // $hub->deleted_by = auth()->id();
            $hub->save();
            $hub->delete();

            return redirect()->route('admin.hubs.index')
                ->with('success', 'Hub moved to trash successfully.');

        } catch (\Exception $e) {
            return redirect()->route('admin.hubs.index')
                ->with('error', 'Failed to delete hub: ' . $e->getMessage());
        }
    }

    /**
     * Display trashed hubs.
     */
    public function trash()
    {
        $hubs = Hub::onlyTrashed()
            ->with(['deleter'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('admin.hubs.trash', compact('hubs'));
    }

    /**
     * Restore a soft deleted hub.
     */
    public function restore($id)
    {
        try {
            $hub = Hub::withTrashed()->findOrFail($id);
            $hub->restore();

            return redirect()->route('admin.hubs.trash')
                ->with('success', 'Hub restored successfully.');

        } catch (\Exception $e) {
            return redirect()->route('admin.hubs.trash')
                ->with('error', 'Failed to restore hub: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a soft deleted hub.
     */
    public function forceDelete($id)
    {
        try {
            $hub = Hub::withTrashed()->findOrFail($id);
            $hub->forceDelete();

            return redirect()->route('admin.hubs.trash')
                ->with('success', 'Hub permanently deleted.');

        } catch (\Exception $e) {
            return redirect()->route('admin.hubs.trash')
                ->with('error', 'Failed to permanently delete hub: ' . $e->getMessage());
        }
    }

    /**
     * Toggle hub status (activate/deactivate)
     */
    public function toggleStatus(Hub $hub)
    {
        $hub->is_active = !$hub->is_active;
        $hub->save();

        $status = $hub->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.hubs.index')
            ->with('success', "Hub {$status} successfully!");
    }

    /**
     * Hubs DataTable - Server Side
     */
    public function getDataTable(Request $request)
    {
        if ($request->ajax()) {
            $hubs = Hub::select('hubs.*');

            return DataTables::of($hubs)
                ->addColumn('riders_count', function($hub) {
                    $count = $hub->riders()->count();
                    return '<span class="badge bg-info">' . $count . '</span>';
                })
                ->addColumn('parcels_count', function($hub) {
                    $count = $hub->sourceParcels()->count();
                    return '<span class="badge bg-secondary">' . $count . '</span>';
                })
                ->addColumn('status_badge', function($hub) {
                    if ($hub->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('action', function($hub) {
                    return '
                        <div class="btn-group" role="group">
                            <a href="' . route('admin.hubs.show', $hub->id) . '" class="btn btn-sm btn-info" title="View">
                                <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                            </a>
                            <a href="' . route('admin.hubs.edit', $hub->id) . '" class="btn btn-sm btn-warning" title="Edit">
                                <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                            </a>
                            <a href="' . route('admin.hubs.toggle-status', $hub->id) . '" class="btn btn-sm ' . ($hub->is_active ? 'btn-secondary' : 'btn-success') . '" title="Toggle Status">
                                <iconify-icon icon="solar:' . ($hub->is_active ? 'power-off-line-duotone' : 'power-on-line-duotone') . '"></iconify-icon>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="confirmDeleteHub(' . $hub->id . ')">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['riders_count', 'parcels_count', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.hubs.datatable');
    }

}
