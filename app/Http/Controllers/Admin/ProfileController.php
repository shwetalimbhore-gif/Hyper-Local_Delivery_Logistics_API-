<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileUpdateRequest;
use App\Http\Requests\Admin\ProfilePictureRequest;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display admin profile page
     */
    public function index()
    {
        $admin = Auth::user();
        return view('admin.profile.index', compact('admin'));
    }

    /**
     * Update admin profile information (Using FormRequest)
     */
    public function update(ProfileUpdateRequest $request)
    {
        $admin = Auth::user();

        try {
            $admin->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'data' => $admin
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update profile picture (Using FormRequest)
     */
    public function updatePicture(ProfilePictureRequest $request)
    {
        $admin = Auth::user();

        try {
            // Delete old image if exists
            if ($admin->profile_image && Storage::disk('public')->exists($admin->profile_image)) {
                Storage::disk('public')->delete($admin->profile_image);
            }

            // Upload new image
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $admin->profile_image = $imagePath;
            $admin->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully!',
                'image_url' => asset('storage/' . $imagePath)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile picture: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change password (Using FormRequest)
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $admin = Auth::user();

        // Check current password
        if (!Hash::check($request->current_password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect!'
            ], 422);
        }

        try {
            $admin->password = Hash::make($request->new_password);
            $admin->save();

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully! Please login again.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage()
            ], 500);
        }
    }
}
