<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\MaterialController;
use App\Http\Controllers\Web\MaterialPurchaseController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ProductionOrderController;
use App\Http\Controllers\Web\StageController;
use App\Http\Controllers\Web\SupplierController;
use Illuminate\Support\Facades\Route;
use LaravelWebauthn\Facades\Webauthn;

// Raíz
Route::get('/', function () {
    return auth()->check() ? redirect('/orders') : redirect('/login');
});

// Auth — sin middleware
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/profile', function( ) {
        return view('profile');
    })->name('profile');

    // ── Rutas compartidas (admin + operativos) ──────────────────
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/orders',    [OrderController::class, 'index']);

    // Ver detalle de orden — whereNumber evita capturar /create
    Route::get('/production-orders/{production_order}',
        [ProductionOrderController::class, 'show'])
        ->whereNumber('production_order');

    // Avanzar etapa — operativos y admin
    Route::post('/production-orders/{productionOrder}/advance-stage',
        [ProductionOrderController::class, 'advanceStage']);

    // API interna
    Route::get('/api/cities/{department}', function ($departmentId) {
        return cache()->remember("cities_{$departmentId}", 3600, fn() =>
            App\Models\City::where('department_id', $departmentId)
                ->orderBy('name')->get(['id', 'name'])
        );
    });

    Route::get('/api/clients/search', function () {
        $q = request('q', '');
        if (strlen($q) < 2) return [];

        return App\Models\Client::where('active', true)
            ->where(fn($query) =>
                $query->where('first_name', 'ilike', "%{$q}%")
                      ->orWhere('last_name',  'ilike', "%{$q}%")
                      ->orWhere('phone',      'ilike', "%{$q}%")
            )
            ->limit(6)
            ->get(['id', 'first_name', 'last_name', 'phone', 'city'])
            ->map(fn($c) => [
                'id'    => $c->id,
                'name'  => $c->first_name.' '.$c->last_name,
                'phone' => $c->phone,
                'city'  => $c->city ?? '',
            ]);
    });

    // ── Solo admin ───────────────────────────────────────────────
    Route::middleware('admin')->group(function () {

        // Órdenes de producción — create debe ir antes de {id}
        Route::get('/production-orders',
            [ProductionOrderController::class, 'index']);
        Route::get('/production-orders/create',
            [ProductionOrderController::class, 'create']);
        Route::post('/production-orders',
            [ProductionOrderController::class, 'store']);
        Route::get('/production-orders/{production_order}/edit',
            [ProductionOrderController::class, 'edit'])
            ->whereNumber('production_order');
        Route::put('/production-orders/{production_order}',
            [ProductionOrderController::class, 'update'])
            ->whereNumber('production_order');
        Route::delete('/production-orders/{production_order}',
            [ProductionOrderController::class, 'destroy'])
            ->whereNumber('production_order');
        Route::post('/production-orders/{productionOrder}/cancel',
            [ProductionOrderController::class, 'cancel']);

        // Pagos
        Route::post('/payments',             [PaymentController::class, 'store']);
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);

        // CRUDs
        Route::resource('/products',  ProductController::class);
        Route::resource('/clients',   ClientController::class);
        Route::resource('/stages',    StageController::class);
        Route::resource('/users',     App\Http\Controllers\Web\UserController::class);

        // Materiales y proveedores
        Route::resource('/suppliers',          SupplierController::class);
        Route::resource('/materials',          MaterialController::class);
        Route::resource('/material-purchases', MaterialPurchaseController::class)
            ->except(['edit', 'update', 'show']);
    });
});