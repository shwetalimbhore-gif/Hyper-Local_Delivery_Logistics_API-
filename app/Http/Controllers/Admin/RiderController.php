<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use App\Models\Hub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RiderController extends Controller
{
    /**
     * Display a listing of riders.
     */
    public function index()
    {
        $riders = Rider::with(['user', 'hub'])->latest()->paginate(15);
        return view('admin.riders.index', compact('riders'));
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
     * Store a newly created rider in storage.
     */
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

            // Create user account
            $user = User::create([
                'role_id' => 2, // Rider role
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
                'is_active' => true,
            ]);

            // Create rider profile
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

    /**
     * Display the specified rider.
     */
    public function show(Rider $rider)
    {
        $rider->load(['user', 'hub', 'assignedParcels' => function($q) {
            $q->latest()->limit(10);
        }]);
        return view('admin.riders.show', compact('rider'));
    }

    /**
     * Show the form for editing the specified rider.
     */
    public function edit(Rider $rider)
    {
        $hubs = Hub::where('is_active', true)->get();
        return view('admin.riders.edit', compact('rider', 'hubs'));
    }

    /**
     * Update the specified rider in storage.
     */
    public function update(Request $request, Rider $rider)
    {
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

            // Update user
            $rider->user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
            ]);

            // Update rider profile
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
     * Remove the specified rider from storage.
     */
    public function destroy(Rider $rider)
    {
        try {
            DB::beginTransaction();

            // Delete user (this will cascade to rider due to foreign key)
            $rider->user->delete();

            DB::commit();

            return redirect()->route('admin.riders.index')
                ->with('success', 'Rider deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete rider: ' . $e->getMessage()]);
        }
    }
}
