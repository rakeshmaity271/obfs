<?php

use Illuminate\Support\Facades\Route;
use LaravelObfuscator\LaravelObfuscator\Http\Controllers\ObfuscatorApiController;
use LaravelObfuscator\LaravelObfuscator\Http\Controllers\UserManagementController;
use LaravelObfuscator\LaravelObfuscator\Http\Controllers\ProjectManagementController;
use LaravelObfuscator\LaravelObfuscator\Http\Controllers\AuditLogController;

/*
|--------------------------------------------------------------------------
| Laravel Obfuscator API Routes
|--------------------------------------------------------------------------
|
| These routes provide the RESTful API for the Laravel Obfuscator package.
| They include file obfuscation, batch processing, and status endpoints.
|
*/

Route::prefix('v1/obfuscator')->name('api.obfuscator.')->group(function () {
    // Main obfuscator API routes
    Route::post('/obfuscate', [ObfuscatorApiController::class, 'obfuscate'])->name('obfuscate');
    Route::post('/batch', [ObfuscatorApiController::class, 'batchObfuscate'])->name('batch');
    Route::get('/status', [ObfuscatorApiController::class, 'status'])->name('status');
    Route::get('/options', [ObfuscatorApiController::class, 'options'])->name('options');
    
    // User Management API routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/regenerate-api-key', [UserManagementController::class, 'regenerateApiKey'])->name('regenerate-api-key');
        Route::get('/{user}/statistics', [UserManagementController::class, 'statistics'])->name('statistics');
    });
    
    // Project Management API routes
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/', [ProjectManagementController::class, 'index'])->name('index');
        Route::post('/', [ProjectManagementController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectManagementController::class, 'show'])->name('show');
        Route::put('/{project}', [ProjectManagementController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{project}/add-files', [ProjectManagementController::class, 'addFiles'])->name('add-files');
        Route::post('/{project}/obfuscate-files', [ProjectManagementController::class, 'obfuscateFiles'])->name('obfuscate-files');
        Route::post('/{project}/deobfuscate-files', [ProjectManagementController::class, 'deobfuscateFiles'])->name('deobfuscate-files');
        Route::get('/{project}/statistics', [ProjectManagementController::class, 'statistics'])->name('statistics');
    });
    
    // Audit Logging API routes
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
        Route::get('/analytics/statistics', [AuditLogController::class, 'analytics'])->name('analytics');
        Route::get('/compliance/report', [AuditLogController::class, 'complianceReport'])->name('compliance-report');
        Route::get('/export/csv', [AuditLogController::class, 'export'])->name('export');
        Route::get('/activity/feed', [AuditLogController::class, 'activityFeed'])->name('activity-feed');
        Route::delete('/cleanup/old', [AuditLogController::class, 'cleanup'])->name('cleanup');
    });
});

// Protected routes (require authentication)
Route::middleware(['auth:sanctum'])->prefix('v1/obfuscator')->name('api.obfuscator.')->group(function () {
    // Add any authenticated-only routes here
    Route::get('/profile', function () {
        return response()->json([
            'success' => true,
            'data' => auth()->user(),
            'message' => 'User profile retrieved successfully'
        ]);
    })->name('profile');
});
