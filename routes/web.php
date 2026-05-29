<?php

use Illuminate\Support\Facades\Route;

// Redirigir raíz al login
Route::get('/', fn() => redirect('/login'));

// Auth web
Route::get('/login',  [App\Http\Controllers\Web\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [App\Http\Controllers\Web\AuthController::class, 'login']);
Route::post('/logout',[App\Http\Controllers\Web\AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Web\DashboardController::class, 'index']);
    Route::resource('/products', App\Http\Controllers\Web\ProductController::class);
    Route::resource('/clients',  App\Http\Controllers\Web\ClientController::class);
    Route::resource('/stages',   App\Http\Controllers\Web\StageController::class);
    Route::resource('/production-orders', App\Http\Controllers\Web\ProductionOrderController::class);
    Route::post('/production-orders/{productionOrder}/advance-stage',
        [App\Http\Controllers\Web\ProductionOrderController::class, 'advanceStage']);
});