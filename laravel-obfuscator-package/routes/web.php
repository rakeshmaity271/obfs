<?php

use Illuminate\Support\Facades\Route;
use LaravelObfuscator\LaravelObfuscator\Http\Controllers\ObfuscatorController;
use LaravelObfuscator\LaravelObfuscator\Http\Controllers\UserManagementController;
use LaravelObfuscator\LaravelObfuscator\Http\Controllers\ProjectManagementController;
use LaravelObfuscator\LaravelObfuscator\Http\Controllers\AuditLogController;

/*
|--------------------------------------------------------------------------
| Laravel Obfuscator Web Routes
|--------------------------------------------------------------------------
|
| These routes provide the web interface for the Laravel Obfuscator package.
| They include the dashboard, file upload, and download functionality.
|
*/

Route::prefix('obfuscator')->name('obfuscator.')->group(function () {
    // Main obfuscator routes
    Route::get('/', [ObfuscatorController::class, 'dashboard'])->name('dashboard');
    Route::post('/upload', [ObfuscatorController::class, 'upload'])->name('upload');
    Route::post('/batch-upload', [ObfuscatorController::class, 'batchUpload'])->name('batch-upload');
    Route::get('/download/{token}', [ObfuscatorController::class, 'download'])->name('download');
    Route::get('/stats', [ObfuscatorController::class, 'stats'])->name('stats');
    
    // User Management routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/regenerate-api-key', [UserManagementController::class, 'regenerateApiKey'])->name('regenerate-api-key');
        Route::get('/{user}/statistics', [UserManagementController::class, 'statistics'])->name('statistics');
    });
    
    // Project Management routes
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
    
    // Audit Logging routes
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
