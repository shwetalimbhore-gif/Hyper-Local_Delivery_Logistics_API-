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
use App\Http\Controllers\TrackingController;

// Tracking Routes (public - no authentication required)
Route::prefix('track')->name('tracking.')->group(function () {
    Route::get('/', [TrackingController::class, 'index'])->name('index');
    Route::post('/track', [TrackingController::class, 'track'])->name('track');
    Route::post('/status', [TrackingController::class, 'getStatus'])->name('status');
});


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

    // // DataTable Routes (add inside admin group)
    // Route::get('/parcels/data', [ParcelController::class, 'getDataTable'])->name('parcels.datatable');
    // Route::get('/riders/data', [RiderController::class, 'getDataTable'])->name('riders.datatable');
    // Route::get('/hubs/data', [HubController::class, 'getDataTable'])->name('hubs.datatable');

    // ========== PARCELS ROUTES - MUST BE IN THIS ORDER ==========
    // Data endpoints (specific routes first)
    Route::get('/parcels/data', [ParcelController::class, 'getData'])->name('parcels.data');
    Route::get('/parcels/trash-data', [ParcelController::class, 'getTrashData'])->name('parcels.trash-data');

    // Trash management
    Route::get('/parcels/trash', [ParcelController::class, 'trash'])->name('parcels.trash');
    Route::post('/parcels/{id}/restore', [ParcelController::class, 'restore'])->name('parcels.restore');
    Route::delete('/parcels/{id}/force-delete', [ParcelController::class, 'forceDelete'])->name('parcels.force-delete');

    // Bulk actions
    Route::post('/parcels/bulk-restore', [ParcelController::class, 'bulkRestore'])->name('parcels.bulk-restore');
    Route::post('/parcels/bulk-force-delete', [ParcelController::class, 'bulkForceDelete'])->name('parcels.bulk-force-delete');

    // Auto assign
    Route::post('/parcels/auto-assign', [ParcelController::class, 'autoAssignAll'])->name('parcels.auto-assign');
    Route::post('/parcels/find-rider', [ParcelController::class, 'findBestRider'])->name('parcels.find-rider');

    // Standard CRUD routes for parcels
    Route::get('/parcels', [ParcelController::class, 'index'])->name('parcels.index');
    Route::get('/parcels/create', [ParcelController::class, 'create'])->name('parcels.create');
    Route::post('/parcels', [ParcelController::class, 'store'])->name('parcels.store');
    Route::get('/parcels/{parcel}', [ParcelController::class, 'show'])->name('parcels.show');
    Route::get('/parcels/{parcel}/edit', [ParcelController::class, 'edit'])->name('parcels.edit');
    Route::put('/parcels/{parcel}', [ParcelController::class, 'update'])->name('parcels.update');
    Route::delete('/parcels/{parcel}', [ParcelController::class, 'destroy'])->name('parcels.destroy');

    /// ========== RIDER ROUTES ==========
    Route::get('/riders/data', [App\Http\Controllers\Admin\RiderController::class, 'getData'])->name('riders.data');
    Route::get('/riders/trash-data', [App\Http\Controllers\Admin\RiderController::class, 'getTrashData'])->name('riders.trash-data');
    Route::get('/riders/trash', [App\Http\Controllers\Admin\RiderController::class, 'trash'])->name('riders.trash');
    Route::put('/riders/restore/{id}', [App\Http\Controllers\Admin\RiderController::class, 'restore'])->name('riders.restore');
    Route::delete('/riders/force-delete/{id}', [App\Http\Controllers\Admin\RiderController::class, 'forceDelete'])->name('riders.force-delete');
    Route::post('/riders/bulk-restore', [App\Http\Controllers\Admin\RiderController::class, 'bulkRestore'])->name('riders.bulk-restore');
    Route::post('/riders/bulk-force-delete', [App\Http\Controllers\Admin\RiderController::class, 'bulkForceDelete'])->name('riders.bulk-force-delete');
    Route::post('/riders/{rider}/toggle-verification', [App\Http\Controllers\Admin\RiderController::class, 'toggleVerification'])->name('riders.toggle-verification');
    Route::post('/riders/{rider}/update-status', [App\Http\Controllers\Admin\RiderController::class, 'updateStatus'])->name('riders.update-status');
    Route::resource('riders', App\Http\Controllers\Admin\RiderController::class);

    /// ========== HUB ROUTES ==========
    // DataTable routes (must be before resource routes)
    Route::get('/hubs/data', [App\Http\Controllers\Admin\HubController::class, 'getData'])->name('hubs.data');
    Route::get('/hubs/trash-data', [App\Http\Controllers\Admin\HubController::class, 'getTrashData'])->name('hubs.trash-data');

    // Trash management routes
    Route::get('/hubs/trash', [App\Http\Controllers\Admin\HubController::class, 'trash'])->name('hubs.trash');
    Route::put('/hubs/restore/{id}', [App\Http\Controllers\Admin\HubController::class, 'restore'])->name('hubs.restore');
    Route::delete('/hubs/force-delete/{id}', [App\Http\Controllers\Admin\HubController::class, 'forceDelete'])->name('hubs.force-delete');

    // Bulk operations
    Route::post('/hubs/bulk-restore', [App\Http\Controllers\Admin\HubController::class, 'bulkRestore'])->name('hubs.bulk-restore');
    Route::post('/hubs/bulk-force-delete', [App\Http\Controllers\Admin\HubController::class, 'bulkForceDelete'])->name('hubs.bulk-force-delete');

    // Toggle status
    Route::get('/hubs/{hub}/toggle-status', [App\Http\Controllers\Admin\HubController::class, 'toggleStatus'])->name('hubs.toggle-status');

    // Resource route (must be last)
    Route::resource('hubs', App\Http\Controllers\Admin\HubController::class);


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

    // Admin Profile Routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('update');
        Route::post('/update-picture', [App\Http\Controllers\Admin\ProfileController::class, 'updatePicture'])->name('update-picture');
        Route::post('/change-password', [App\Http\Controllers\Admin\ProfileController::class, 'changePassword'])->name('change-password');
    });
});


// Rider routes (protected by auth and role check)
Route::middleware(['auth'])->prefix('rider')->name('rider.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [RiderRiderController::class, 'dashboard'])->name('dashboard');

    // Parcel Management
    Route::get('/parcels/data', [RiderRiderController::class, 'getParcelsData'])->name('parcels.data');
    Route::get('/parcels', [RiderRiderController::class, 'parcels'])->name('parcels.index');
    Route::post('/parcels/{parcel}/update-status', [RiderRiderController::class, 'updateParcelStatus'])->name('parcels.update-status');
    Route::get('/parcels/{parcel}/available-statuses', [RiderRiderController::class, 'getAvailableStatuses'])->name('parcels.available-statuses');

    // Earnings
    Route::get('/earnings', [RiderRiderController::class, 'earnings'])->name('earnings');

    // Profileff
    Route::get('/profile', [RiderRiderController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [RiderRiderController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/update-image', [RiderRiderController::class, 'updateProfileImage'])->name('profile.update-image');


    // Status
    Route::post('/update-status', [RiderRiderController::class, 'updateStatus'])->name('update-status');

    Route::post('/notification/read', [RiderRiderController::class, 'markNotificationRead'])->name('notification.read');
    Route::post('/notifications/read-all', [RiderController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
});




// Home route - redirect based on auth status
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});
