<?php

use App\Modules\Production\Controllers\ProductionOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('ability:production-orders:read')->group(function () {
        Route::apiResource('production-orders', ProductionOrderController::class)
            ->only(['index', 'show']);
    });

    // Avanzar etapa — cualquier autenticado; la habilidad por etapa
    // (o el rol admin) se valida dentro del servicio (canAdvanceStage()).
    Route::post('production-orders/{productionOrder}/advance-stage',
        [ProductionOrderController::class, 'advanceStage']);

    // Solo admin puede crear/editar órdenes y cancelarlas
    Route::middleware('admin')->group(function () {
        Route::apiResource('production-orders', ProductionOrderController::class)
            ->only(['store', 'update']);

        Route::post('production-orders/{productionOrder}/cancel',
            [ProductionOrderController::class, 'cancel']);
    });
});