<?php

use App\Http\Controllers\Api\SystemToolsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('admin')->group(function () {
        // Tools
        Route::prefix('tools')->group(function () {
            Route::get('finalize-update', [SystemToolsController::class, 'finalizeUpdate']);
            Route::get('json-language', [SystemToolsController::class, 'i18n']);
            Route::get('storage-link', [SystemToolsController::class, 'storageLink']);
            Route::get('clear-cache', [SystemToolsController::class, 'clearCache']);
            Route::get('migrate', [SystemToolsController::class, 'migrate']);
            Route::get('optimize', [SystemToolsController::class, 'optimize']);
            Route::get('seed-mailables', [SystemToolsController::class, 'seedMailableTemplates']);
        });
    });
});
