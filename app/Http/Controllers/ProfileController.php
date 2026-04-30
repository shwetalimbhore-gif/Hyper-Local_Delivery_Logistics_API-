<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display user profile from database
     */
    public function index()
    {
        // Get the authenticated user with all data
        $user = Auth::user();

        // Debug: Check if user is loaded
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        // Load user data with relationships if needed
        // $user->load('role'); // If you have role relationship

        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update user profile in database
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return redirect()->back()->with('error', 'User not found');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
            ]);

            // Update user in database
            $updated = $user->update($validated);

            if ($updated) {
                return redirect()->back()->with('success', 'Profile updated successfully!');
            } else {
                return redirect()->back()->with('error', 'Failed to update profile');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Database error: ' . $e->getMessage());
        }
    }

    /**
     * Update profile picture
     */
    public function updatePicture(Request $request)
    {
        try {
            $request->validate([
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = Auth::user();

            if ($request->hasFile('profile_image')) {
                // Delete old image
                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                // Upload new image
                $path = $request->file('profile_image')->store('profile_images', 'public');
                $user->profile_image = $path;
                $user->save();
            }

            return redirect()->back()->with('success', 'Profile picture updated!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to upload image: ' . $e->getMessage());
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'password' => 'required|min:8|confirmed',
            ]);

            $user = Auth::user();

            // Check current password
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
            }

            // Update password
            $user->password = Hash::make($request->password);
            $user->save();

            return redirect()->back()->with('success', 'Password changed successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to change password: ' . $e->getMessage());
        }
    }
}
