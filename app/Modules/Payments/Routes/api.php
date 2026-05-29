<?php

use App\Modules\Payments\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Pagos de una orden específica
    Route::get('production-orders/{productionOrder}/payments',
        [PaymentController::class, 'index']);

    // Crear y eliminar pagos
    Route::post('payments', [PaymentController::class, 'store']);
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy']);
});
