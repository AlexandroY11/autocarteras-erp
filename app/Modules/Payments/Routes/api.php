<?php

use App\Modules\Payments\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Pagos de una orden específica
    Route::middleware('ability:payments:read')->group(function () {
        Route::get('production-orders/{productionOrder}/payments',
            [PaymentController::class, 'index']);
    });

    // Solo admin puede registrar pagos
    Route::middleware('admin')->group(function () {
        Route::post('payments', [PaymentController::class, 'store']);
    });
});
