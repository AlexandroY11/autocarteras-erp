<?php

use App\Modules\Clients\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('ability:clients:read')->group(function () {
        Route::apiResource('clients', ClientController::class)->only(['index', 'show']);
    });

    // Solo admin puede crear/editar/eliminar clientes
    Route::middleware('admin')->group(function () {
        Route::apiResource('clients', ClientController::class)->except(['index', 'show']);
    });
});
