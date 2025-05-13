<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LazadaAuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::redirect('/', '/login');

// Laravel 11 Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    
    // Password update
    Route::get('password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

// Products routes
Route::middleware(['auth'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    
    // Routes that need Lazada token
    Route::middleware(['lazada.token'])->group(function () {
        Route::get('/products/sync', [ProductController::class, 'sync'])->name('products.sync');
        Route::get('/products/{product}/edit-stock', [ProductController::class, 'editStock'])->name('products.edit-stock');
        Route::put('/products/{product}/update-stock', [ProductController::class, 'updateStock'])->name('products.update-stock');
        
        // Stock Adjustments
        Route::get('/products/{product}/adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
        Route::get('/products/{product}/adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
        Route::post('/products/{product}/adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
    });
});

// Orders routes
Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    
    // Routes that need Lazada token
    Route::middleware(['lazada.token'])->group(function () {
        Route::get('/orders/sync', [OrderController::class, 'sync'])->name('orders.sync');
        Route::get('/orders/{order}/edit-status', [OrderController::class, 'editStatus'])->name('orders.edit-status');
        Route::put('/orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    });
});

// Settings routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
});

// Lazada Auth routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/lazada/auth', [LazadaAuthController::class, 'redirect'])->name('lazada.auth');
    Route::get('/lazada/callback', [LazadaAuthController::class, 'callback'])->name('lazada.callback');
});