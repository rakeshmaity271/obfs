<?php

use Illuminate\Support\Facades\Route;
use LaravelObfuscator\LaravelObfuscator\Http\Controllers\ObfuscatorApiController;

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
    
    // Single file obfuscation
    Route::post('/obfuscate', [ObfuscatorApiController::class, 'obfuscate'])->name('obfuscate');
    
    // Batch obfuscation
    Route::post('/batch', [ObfuscatorApiController::class, 'batchObfuscate'])->name('batch');
    
    // Status and information
    Route::get('/status', [ObfuscatorApiController::class, 'status'])->name('status');
    Route::get('/options', [ObfuscatorApiController::class, 'options'])->name('options');
    
    // Note: Authentication middleware is applied in the controller constructor
    // These routes require a valid Sanctum token
});
