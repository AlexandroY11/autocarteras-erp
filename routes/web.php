<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ProductionOrderController;
use App\Http\Controllers\Web\StageController;
use Illuminate\Support\Facades\Route;

// Redirigir raíz al login
Route::get('/', fn () => redirect('/login'));

// Auth web
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('/products', ProductController::class);
    Route::resource('/clients', ClientController::class);
    Route::resource('/stages', StageController::class);
    Route::resource('/production-orders', ProductionOrderController::class);
    Route::post('/production-orders/{productionOrder}/advance-stage',
        [ProductionOrderController::class, 'advanceStage']);
    Route::post('/production-orders/{productionOrder}/cancel',
        [ProductionOrderController::class, 'cancel']);
    Route::get('/api/cities/{department}', function ($departmentId) {
        return cache()->remember("cities_{$departmentId}", 3600, function () use ($departmentId) {
            return \App\Models\City::where('department_id', $departmentId)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    });
    Route::resource('/users', App\Http\Controllers\Web\UserController::class);

    Route::get('/api/clients/search', function () {
        $q = request('q', '');
        if (strlen($q) < 2) return [];

        return \App\Models\Client::where('active', true)
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'ilike', "%{$q}%")
                    ->orWhere('last_name',  'ilike', "%{$q}%")
                    ->orWhere('phone',      'ilike', "%{$q}%");
            })
            ->limit(6)
            ->get(['id', 'first_name', 'last_name', 'phone', 'city'])
            ->map(fn($c) => [
                'id'    => $c->id,
                'name'  => $c->first_name . ' ' . $c->last_name,
                'phone' => $c->phone,
                'city'  => $c->city ?? '',
            ]);
    });
});
