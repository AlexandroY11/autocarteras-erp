<?php

use App\Modules\Products\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('ability:products:read')->group(function () {
        Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    });

    // Solo admin puede crear/editar/eliminar productos
    Route::middleware('admin')->group(function () {
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    });
});
