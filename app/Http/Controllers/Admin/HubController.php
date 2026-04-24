<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hub;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Admin\HubStoreRequest;
use App\Http\Requests\Admin\HubUpdateRequest;
use Illuminate\Support\Facades\Log;

class HubController extends Controller
{
    /**
     * Display a listing of hubs.
     */
    public function index()
    {
        return view('admin.hubs.index');
    }

    /**
     * Get hubs data for DataTable via AJAX
     */
    public function getData(Request $request)
    {
        try {
            $hubs = Hub::select([
                'id',
                'code',
                'name',
                'manager_name',
                'phone',
                'email',
                'is_active',
                'address',
                'created_at'
            ]);

            return DataTables::eloquent($hubs)
                ->addColumn('riders_count', function($row) {
                    $count = $row->riders()->count();
                    return '<span class="badge bg-info"><iconify-icon icon="solar:bicycle-line-duotone" class="me-1"></iconify-icon>' . $count . '</span>';
                })
                ->addColumn('parcels_count', function($row) {
                    $count = $row->sourceParcels()->count();
                    return '<span class="badge bg-secondary"><iconify-icon icon="solar:box-line-duotone" class="me-1"></iconify-icon>' . $count . '</span>';
                })
                ->addColumn('status_badge', function($row) {
                    if ($row->is_active) {
                        return '<span class="badge bg-success"><iconify-icon icon="solar:check-circle-line-duotone" class="me-1"></iconify-icon>Active</span>';
                    } else {
                        return '<span class="badge bg-danger"><iconify-icon icon="solar:close-circle-line-duotone" class="me-1"></iconify-icon>Inactive</span>';
                    }
                })
                ->addColumn('action', function($row) {
                    $csrf = csrf_token();
                    $toggleIcon = $row->is_active ? 'solar:power-off-line-duotone' : 'solar:power-on-line-duotone';
                    $toggleClass = $row->is_active ? 'btn-secondary' : 'btn-success';
                    $toggleText = $row->is_active ? 'Deactivate' : 'Activate';

                    return '
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="' . route('admin.hubs.show', $row->id) . '" class="btn btn-info btn-sm" title="View">
                                <iconify-icon icon="solar:eye-line-duotone"></iconify-icon>
                            </a>
                            <a href="' . route('admin.hubs.edit', $row->id) . '" class="btn btn-warning btn-sm" title="Edit">
                                <iconify-icon icon="solar:pen-line-duotone"></iconify-icon>
                            </a>
                            <a href="' . route('admin.hubs.toggle-status', $row->id) . '" class="btn ' . $toggleClass . ' btn-sm toggle-status-btn"
                               data-id="' . $row->id . '"
                               data-code="' . e($row->code) . '"
                               title="' . $toggleText . '">
                                <iconify-icon icon="' . $toggleIcon . '"></iconify-icon>
                            </a>
                            <button type="button" class="btn btn-danger btn-sm soft-delete-btn"
                                    data-id="' . $row->id . '"
                                    data-code="' . e($row->code) . '"
                                    title="Move to Trash">
                                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['riders_count', 'parcels_count', 'status_badge', 'action'])
                ->toJson();

        } catch (\Exception $e) {
            Log::error('Hub DataTable Error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Failed to load hubs data: ' . $e->getMessage()
            ], 500);
        }
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
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete hub because it has assigned riders. Please reassign or delete the riders first.'
                ], 400);
            }

            // Check if hub has any parcels
            if ($hub->sourceParcels()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete hub because it has associated parcels. Please reassign or delete the parcels first.'
                ], 400);
            }

            // Soft delete the hub
            $hub->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hub moved to trash successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hub: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display trashed hubs.
     */
    public function trash()
    {
        return view('admin.hubs.trash');
    }

    /**
     * Get trashed hubs data for DataTable via AJAX
     */
    public function getTrashData(Request $request)
    {
        try {
            $hubs = Hub::onlyTrashed()
                ->select('id', 'code', 'name', 'manager_name', 'deleted_at');

            return DataTables::eloquent($hubs)
                ->addColumn('checkbox', function($row) {
                    return '<input type="checkbox" class="hub-checkbox" value="' . $row->id . '">';
                })
                ->editColumn('deleted_at', function($row) {
                    return $row->deleted_at->format('d M Y, h:i A');
                })
                ->addColumn('actions', function($row) {
                    return '
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-success btn-sm restore-btn"
                                    data-id="' . $row->id . '"
                                    data-code="' . e($row->code) . '">
                                <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                                Restore
                            </button>
                            <button type="button" class="btn btn-danger btn-sm force-delete-btn"
                                    data-id="' . $row->id . '"
                                    data-code="' . e($row->code) . '">
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
     * Restore a soft deleted hub.
     */
    public function restore($id)
    {
        try {
            $hub = Hub::withTrashed()->findOrFail($id);
            $hub->restore();

            return response()->json([
                'success' => true,
                'message' => 'Hub restored successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore hub: ' . $e->getMessage()
            ], 500);
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

            return response()->json([
                'success' => true,
                'message' => 'Hub permanently deleted.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete hub: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle hub status (activate/deactivate)
     */
    public function toggleStatus(Hub $hub)
    {
        try {
            $hub->is_active = !$hub->is_active;
            $hub->save();

            $status = $hub->is_active ? 'activated' : 'deactivated';

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Hub {$status} successfully!",
                    'is_active' => $hub->is_active
                ]);
            }

            return redirect()->route('admin.hubs.index')
                ->with('success', "Hub {$status} successfully!");

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to toggle status: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.hubs.index')
                ->with('error', 'Failed to toggle status: ' . $e->getMessage());
        }
    }

    /**
     * Bulk restore multiple hubs
     */
    public function bulkRestore(Request $request)
    {
        try {
            $ids = $request->ids;
            Hub::onlyTrashed()->whereIn('id', $ids)->restore();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' hubs restored successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore hubs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk force delete multiple hubs
     */
    public function bulkForceDelete(Request $request)
    {
        try {
            $ids = $request->ids;
            Hub::onlyTrashed()->whereIn('id', $ids)->forceDelete();

            return response()->json([
                'success' => true,
                'message' => count($ids) . ' hubs permanently deleted.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hubs: ' . $e->getMessage()
            ], 500);
        }
    }
}
