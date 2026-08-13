<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileDashboardController;
use App\Http\Controllers\Api\MobileProfileController;
use App\Http\Controllers\Api\MobilePayslipController;
use App\Http\Controllers\Api\MobileAttendanceController;
use App\Http\Controllers\Api\MobileLeaveController;
use App\Http\Controllers\Api\MobileTravelOrderController;
use App\Http\Controllers\Api\MobileTrainingController;
use App\Http\Controllers\Api\MobileChatbotController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AiAssistantController;

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

// Public AI assistant for the welcome page's chat widget. No user, so it can
// only answer policy/how-to/general questions — HrChatbotAnswerer never runs
// generated SQL for a null caller, and no data capability is reachable without
// a user. Throttled harder than the authenticated endpoints because there is
// no login to slow down an abuser spending the shared org API key.
Route::post('/public/chatbot/chat', [\App\Http\Controllers\PublicChatbotController::class, 'chat'])
    ->middleware('throttle:10,1')
    ->name('public.chatbot.chat');

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

        // Leave & benefits
        Route::get('/leave', [MobileLeaveController::class, 'index']);
        Route::post('/leave', [MobileLeaveController::class, 'store']);
        Route::post('/leave/{id}/cancel', [MobileLeaveController::class, 'cancel']);

        // Travel orders
        Route::get('/travel-orders', [MobileTravelOrderController::class, 'index']);
        Route::get('/travel-orders/{id}', [MobileTravelOrderController::class, 'show']);
        Route::post('/travel-orders', [MobileTravelOrderController::class, 'store']);
        Route::delete('/travel-orders/{id}', [MobileTravelOrderController::class, 'destroy']);

        // Training / L&D
        Route::get('/training', [MobileTrainingController::class, 'index']);
        Route::get('/training/{id}', [MobileTrainingController::class, 'show']);
        Route::post('/training', [MobileTrainingController::class, 'store']);
        Route::delete('/training/{id}', [MobileTrainingController::class, 'destroy']);
        Route::get('/training/{id}/certificate', [MobileTrainingController::class, 'certificate']);

        // HR chatbot (employee-scoped)
        Route::post('/chatbot', [MobileChatbotController::class, 'chat'])->middleware('throttle:20,1');
    });

    // AI Assistant — same AiQueryService the web UI uses, so permissions and
    // audit logging are identical across both surfaces.
    Route::prefix('ai')->group(function () {
        // Same reasoning as the web surface: one question can spend several
        // provider calls against a shared org API key.
        Route::post('/query', [AiAssistantController::class, 'query'])->middleware('throttle:20,1');
        Route::get('/export/{token}', [AiAssistantController::class, 'export']);
        // Same AiFileResolver + AiAccessPolicy gate as the web surface.
        Route::get('/file/{source}/{ref}', [AiAssistantController::class, 'file'])
            ->where('source', '[a-z_]+')
            ->where('ref', '[A-Za-z0-9_\-]+')
            ->name('api.ai.file');
        Route::get('/conversations', [AiAssistantController::class, 'index']);
        Route::get('/conversations/{conversation}', [AiAssistantController::class, 'show']);
        Route::delete('/conversations/{conversation}', [AiAssistantController::class, 'destroy']);
    });
});
