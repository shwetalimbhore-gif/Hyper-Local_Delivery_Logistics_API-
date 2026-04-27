<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ParcelController;
use App\Http\Controllers\API\RiderController;
use App\Http\Controllers\API\ReportController;

// API Routes

// ==================== PUBLIC ROUTES ====================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/track', [ParcelController::class, 'track']);

// Test endpoint
Route::get('/test', function() {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'version' => '1.0.0'
    ]);
});

// ==================== PROTECTED ROUTES ====================
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/upload-image', [AuthController::class, 'uploadProfileImage']);

    // Parcel routes
    Route::get('/parcels', [ParcelController::class, 'index']);
    Route::get('/parcels/{id}', [ParcelController::class, 'show']);
    Route::post('/parcels', [ParcelController::class, 'store']);
    Route::put('/parcels/{id}', [ParcelController::class, 'update']);
    Route::delete('/parcels/{id}', [ParcelController::class, 'destroy']);
    Route::patch('/parcels/{id}/status', [ParcelController::class, 'updateStatus']);

    // Rider specific routes
    Route::get('/my-parcels', [RiderController::class, 'myParcels']);
    Route::get('/my-earnings', [RiderController::class, 'myEarnings']);
    Route::patch('/rider/status', [RiderController::class, 'updateStatus']);

    // Admin reports (admin only)
    Route::middleware('admin')->group(function () {
        Route::get('/reports/earnings', [ReportController::class, 'earnings']);
        Route::get('/reports/delivery', [ReportController::class, 'delivery']);
    });
});
