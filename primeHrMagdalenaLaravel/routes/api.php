<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileDashboardController;
use App\Http\Controllers\Api\MobileProfileController;
use App\Http\Controllers\Api\MobilePayslipController;
use App\Http\Controllers\Api\MobileAttendanceController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String()
    ]);
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Mobile Dashboard API
    Route::prefix('mobile')->group(function () {
        // Dashboard data
        Route::get('/dashboard', [MobileDashboardController::class, 'index']);
        
        // Deductions
        Route::get('/deductions', [MobileDashboardController::class, 'deductions']);
        
        // Leave balances
        Route::get('/leave-balances', [MobileDashboardController::class, 'leaveBalances']);
        
        // Chart data
        Route::get('/charts', [MobileDashboardController::class, 'charts']);
        
        // Clear cache
        Route::post('/clear-cache', [MobileDashboardController::class, 'clearCache']);

        // Employee profile
        Route::get('/profile', [MobileProfileController::class, 'show']);

        // Payslips
        Route::get('/payslips', [MobilePayslipController::class, 'index']);
        Route::get('/payslips/{id}', [MobilePayslipController::class, 'show']);

        // Attendance
        Route::get('/attendance', [MobileAttendanceController::class, 'index']);
        Route::get('/attendance/detailed', [MobileAttendanceController::class, 'detailed']);
    });
});
