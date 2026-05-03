<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\JsonController;
use Illuminate\Support\Facades\Route;

// ========== ADMIN AUTH ROUTES ==========
Route::post('/admin/login', [App\Http\Controllers\Api\AdminAuthController::class, 'login']);
Route::post('/admin/logout', [App\Http\Controllers\Api\AdminAuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/admin/user', [App\Http\Controllers\Api\AdminAuthController::class, 'user'])->middleware('auth:sanctum');

// ========== ADMIN API ROUTES ==========
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'stats']);
    Route::get('/products', [AdminController::class, 'products']);
    Route::post('/products', [AdminController::class, 'storeProduct']);
    Route::put('/products/{id}', [AdminController::class, 'updateProduct']);
    Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);
    Route::get('/orders', [AdminController::class, 'orders']);
    Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
});

// ========== PUBLIC API ROUTES (No Authentication Required) ==========

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

// Product Routes (Public - anyone can view products)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/category/{category}', [ProductController::class, 'byCategory']);

// JSON Operations (For Assignment Requirements - Public)
Route::get('/products/json/export', [JsonController::class, 'exportProducts']);
Route::get('/products/json/consume', [JsonController::class, 'getProductsJson']);

// ========== PROTECTED API ROUTES (Authentication Required) ==========
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth (logout, get user)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Cart Routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::get('/cart/count', [CartController::class, 'count']);
    Route::get('/cart/summary', [CartController::class, 'summary']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update/{id}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove']);
    
    // Order Routes
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/history', [OrderController::class, 'history']);
    Route::get('/orders/{id}/status', [OrderController::class, 'status']);
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    
    // Admin Routes (Require admin role - role_level 1 or 2)
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/stats', [AdminController::class, 'stats']);
        
        // Product Management
        Route::get('/products', [AdminController::class, 'products']);
        Route::post('/products', [AdminController::class, 'storeProduct']);
        Route::put('/products/{id}', [AdminController::class, 'updateProduct']);
        Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);

Route::post('/products/{id}/image', [ProductController::class, 'uploadImage']);
Route::get('/products/{id}/image', [App\Http\Controllers\Api\ProductController::class, 'uploadImage']);
        
        // Order Management
        Route::get('/orders', [AdminController::class, 'orders']);
        Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
        
        // User Management
        Route::get('/users', [AdminController::class, 'users']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        
        // Admin Management (Super Admin only - role_level 1)
        Route::get('/admins', [AdminController::class, 'admins']);
        Route::post('/admins', [AdminController::class, 'storeAdmin']);
        Route::delete('/admins/{id}', [AdminController::class, 'deleteAdmin']);
    });
});