<?php

use Illuminate\Support\Facades\Route;
use LaravelObfuscator\LaravelObfuscator\Http\Controllers\ObfuscatorController;

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
    
    // Main dashboard
    Route::get('/', [ObfuscatorController::class, 'dashboard'])->name('dashboard');
    
    // File operations
    Route::post('/upload', [ObfuscatorController::class, 'upload'])->name('upload');
    Route::post('/batch-upload', [ObfuscatorController::class, 'batchUpload'])->name('batch-upload');
    Route::get('/download/{token}', [ObfuscatorController::class, 'download'])->name('download');
    
    // Statistics
    Route::get('/stats', [ObfuscatorController::class, 'stats'])->name('stats');
    
    // Optional: Add middleware for authentication if needed
    // Route::middleware(['auth'])->group(function () {
    //     Route::get('/', [ObfuscatorController::class, 'dashboard'])->name('dashboard');
    //     Route::post('/upload', [ObfuscatorController::class, 'upload'])->name('upload');
    //     Route::post('/batch-upload', [ObfuscatorController::class, 'batchUpload'])->name('batch-upload');
    //     Route::get('/download/{token}', [ObfuscatorController::class, 'download'])->name('download');
    //     Route::get('/stats', [ObfuscatorController::class, 'stats'])->name('stats');
    // });
});
