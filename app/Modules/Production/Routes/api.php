<?php

use App\Modules\Production\Controllers\ProductionOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('production-orders', ProductionOrderController::class);

    // Acciones especiales
    Route::post('production-orders/{productionOrder}/advance-stage',
        [ProductionOrderController::class, 'advanceStage']);

    Route::post('production-orders/{productionOrder}/cancel',
        [ProductionOrderController::class, 'cancel']);
});