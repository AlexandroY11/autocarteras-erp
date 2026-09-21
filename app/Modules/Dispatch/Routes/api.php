<?php

use App\Modules\Dispatch\Controllers\DispatchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('production-orders/{productionOrder}/dispatch',
        [DispatchController::class, 'dispatch']);

    Route::post('production-orders/{productionOrder}/guide-number',
        [DispatchController::class, 'setGuideNumber']);

    Route::post('production-orders/{productionOrder}/mark-sent',
        [DispatchController::class, 'markSent']);

    Route::post('production-orders/{productionOrder}/mark-delivered',
        [DispatchController::class, 'markDelivered']);

    Route::post('production-orders/{productionOrder}/mark-returned',
        [DispatchController::class, 'markReturned']);

    Route::post('production-orders/{productionOrder}/return-to-pending-dispatch',
        [DispatchController::class, 'returnToPendingDispatch']);
});
