<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Rider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already logged in, redirect based on role
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Redirect based on user role
            return $this->redirectBasedOnRole();
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Redirect users based on their role
     */
    private function redirectBasedOnRole()
    {
        $user = Auth::user();

        // Check if user has role relationship
        if ($user->role) {
            if ($user->role->slug === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role->slug === 'rider') {
                return redirect()->route('rider.dashboard');
            }
        }

        // Default fallback
        return redirect('/dashboard');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',  // Uncommented - this is required
            'address' => 'nullable|string',       // Added address field
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'role_id' => 2, // Rider role
                'is_active' => true,
            ]);

            // Create rider profile (REQUIRED for riders)
            Rider::create([
                'user_id' => $user->id,
                'employee_id' => 'RID' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                'vehicle_type' => 'bike',
                'status' => 'available',
                'max_weight_capacity' => 50,
                'max_size_capacity' => 100,
                'is_verified' => true,
                'joined_date' => now(),
            ]);

            DB::commit();

            // DO NOT auto-login - redirect to login page with success message
            return redirect()->route('login')
                ->with('success', 'Registration successful! Please login with your credentials.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
