<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hub;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Admin\HubStoreRequest;
use App\Http\Requests\Admin\HubUpdateRequest;

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

    public function getData(Request $request){
        $hubs = Hub::select(['id', 'code', 'name', 'manager_name', 'phone', 'email', 'is_active']);

        $csrf = csrf_token();

        return DataTables::eloquent($hubs)
            ->addColumn('riders_count', fn($row) => '<span class="badge bg-info">' . $row->riders()->count() . '</span>')
            ->addColumn('parcels_count', fn($row) => '<span class="badge bg-secondary">' . $row->sourceParcels()->count() . '</span>')
            ->addColumn('status_badge', fn($row) => $row->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>')
            ->addColumn('action', fn($row) =>
                '<div class="btn-group" role="group">
                    <a href="/admin/hubs/'.$row->id.'" class="btn btn-sm btn-info">View</a>
                    <a href="/admin/hubs/'.$row->id.'/edit" class="btn btn-sm btn-warning">Edit</a>
                    <a href="/admin/hubs/'.$row->id.'/toggle-status" class="btn btn-sm ' . ($row->is_active ? 'btn-secondary' : 'btn-success') . '">' . ($row->is_active ? 'Deactivate' : 'Activate') . '</a>
                    <form method="POST" action="/admin/hubs/'.$row->id.'" style="display:inline;">
                        <input type="hidden" name="_token" value="'.$csrf.'">
                        <input type="hidden" name="_method" value="DELETE">
                        <button class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</button>
                    </form>
                </div>'
            )
            ->rawColumns(['riders_count', 'parcels_count', 'status_badge', 'action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new hub.
     */
    public function create()
    {
        return view('admin.hubs.create');
    }

    /**
     * Store a newly created hub (Using FormRequest)
     */
    public function store(HubStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active');

        $hub = Hub::create($validated);

        return redirect()->route('admin.hubs.index')
            ->with('success', 'Hub created successfully! Code: ' . $hub->code);
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
     * Update the specified hub (Using FormRequest)
     */
    public function update(HubUpdateRequest $request, $id)
    {
        $hub = Hub::findOrFail($id);
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active');

        $hub->update($validated);

        return redirect()->route('admin.hubs.index')
            ->with('success', 'Hub updated successfully');
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
