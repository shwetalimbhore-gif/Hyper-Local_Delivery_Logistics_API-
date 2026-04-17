<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            // Ensure is_active is properly set (checkbox returns 1 or 0)
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
            // FIXED: Properly handle the is_active checkbox value
            // When checkbox is checked, it sends value "1" or "on"
            // When unchecked, the hidden input sends "0"
            $validated['is_active'] = $request->has('is_active') && $request->input('is_active') == 1;

            $hub->update($validated);

            return redirect()->route('admin.hubs.index')
                ->with('success', 'Hub updated successfully! Status is now ' . ($hub->is_active ? 'ACTIVE' : 'INACTIVE'));

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update hub: ' . $e->getMessage()]);
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
     * Remove the specified hub from storage (soft delete).
     */
    public function destroy(Hub $hub)
    {
        try {
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
            $hub->deleted_by = auth()->id();
            $hub->save();
            $hub->delete();

            return redirect()->route('admin.hubs.index')
                ->with('success', 'Hub moved to trash successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete hub: ' . $e->getMessage()]);
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
}
