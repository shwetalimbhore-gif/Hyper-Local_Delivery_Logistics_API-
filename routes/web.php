<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ParcelController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RiderController;
use App\Http\Controllers\Admin\HubController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Rider\RiderController as RiderRiderController;

// Test mail route
Route::get('/test-mail', function () {
    try {
        Mail::raw('Test email from Laravel with Mailtrap!', function ($message) {
            $message->to('test@example.com')
                    ->subject('Mailtrap Test Email');
        });
        return 'Email sent! Check your Mailtrap inbox.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Guest routes (not logged in)
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Register routes
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Forgot password routes
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Admin routes (protected by auth and role)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Parcel Management
    Route::resource('parcels', ParcelController::class);

    // Rider Management
    Route::resource('riders', RiderController::class);

    // Hub Management
    Route::resource('hubs', HubController::class);
    Route::get('/hubs/{hub}/toggle-status', [HubController::class, 'toggleStatus'])->name('hubs.toggle-status');

    Route::get('/notifications/fetch', [DashboardController::class, 'fetchNotifications'])->name('notifications.fetch');
    Route::post('/notification/read', [DashboardController::class, 'markNotificationRead'])->name('notification.read');
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');



    // Reports Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/earnings', [App\Http\Controllers\Admin\ReportController::class, 'earnings'])->name('earnings');
        Route::get('/delivery', [App\Http\Controllers\Admin\ReportController::class, 'delivery'])->name('delivery');
        Route::get('/earnings/export', [App\Http\Controllers\Admin\ReportController::class, 'exportEarnings'])->name('earnings.export');
        Route::get('/delivery/export', [App\Http\Controllers\Admin\ReportController::class, 'exportDelivery'])->name('delivery.export');
    });
});


// Rider routes (protected by auth and role check)
Route::middleware(['auth'])->prefix('rider')->name('rider.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [RiderRiderController::class, 'dashboard'])->name('dashboard');

    // Parcel Management
    Route::get('/parcels', [RiderRiderController::class, 'parcels'])->name('parcels.index');
    Route::post('/parcels/{parcel}/update-status', [RiderRiderController::class, 'updateParcelStatus'])->name('parcels.update-status');
    Route::get('/parcels/{parcel}/available-statuses', [RiderRiderController::class, 'getAvailableStatuses'])->name('parcels.available-statuses');

    // Earnings
    Route::get('/earnings', [RiderRiderController::class, 'earnings'])->name('earnings');

    // Profile
    Route::get('/profile', [RiderRiderController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [RiderRiderController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/update-image', [RiderController::class, 'updateProfileImage'])->name('profile.update-image');


    // Status
    Route::post('/update-status', [RiderRiderController::class, 'updateStatus'])->name('update-status');

    Route::post('/notification/read', [RiderController::class, 'markNotificationRead'])->name('notification.read');
    Route::post('/notifications/read-all', [RiderController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
});



// Route::get('/dashboard');

// Home route - redirect based on auth status
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});
