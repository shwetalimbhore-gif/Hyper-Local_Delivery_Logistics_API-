<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RiderController;
use App\Http\Controllers\Admin\HubController;

use App\Http\Controllers\AuthController;


Route::get('/', [DashboardController::class , 'index'])->name('admin.dashboard')->middleware('auth');
// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});


// // Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Home
// Route::get('/', function () {
//     return view('welcome');

// });

// Route::get('/auth/google', [GoogleController::class, 'redirect']);
// Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });


Route::middleware('auth')->prefix('admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class , 'index'])
    ->name('admin.dashboard');

    // Route::get('/orders', function () {
    //     return view('admin.orders.index');
    // })->name('admin.orders');

    // Route::get('/riders', function () {
    //     return view('admin.riders.index');
    // })->name('admin.riders');

    Route::resource('riders', RiderController::class);
    Route::resource('hubs', HubController::class);
    Route::resource('orders', OrderController::class);

    // Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders');
    // Route::get('/orders/create', [OrderController::class, 'create'])->name('admin.orders.create');
    // Route::post('/orders/store', [OrderController::class, 'store'])->name('admin.orders.store');
    // Route::get('/orders/edit/{id}', [OrderController::class, 'edit'])->name('admin.orders.edit');
    // Route::post('/orders/update/{id}', [OrderController::class, 'update'])->name('admin.orders.update');
    // Route::get('/orders/delete/{id}', [OrderController::class, 'delete'])->name('admin.orders.delete');
    // Route::get('/orders/{id}/assign-rider', [OrderController::class, 'assignNearestRider'])
    // ->name('orders.assignRider');

});


// require __DIR__.'/auth.php';
