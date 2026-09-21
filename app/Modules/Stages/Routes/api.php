<?php

use App\Modules\Stages\Controllers\StageController;
use Illuminate\Support\Facades\Route;

// Todos pueden ver las etapas, siempre que el token tenga la ability stages:read
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('ability:stages:read')->group(function () {
        Route::get('stages', [StageController::class, 'index']);
        Route::get('stages/{stage}', [StageController::class, 'show']);
    });

    // Solo admin puede modificar etapas
    Route::middleware('admin')->group(function () {
        Route::post('stages', [StageController::class, 'store']);
        Route::put('stages/{stage}', [StageController::class, 'update']);
        Route::delete('stages/{stage}', [StageController::class, 'destroy']);
    });
});