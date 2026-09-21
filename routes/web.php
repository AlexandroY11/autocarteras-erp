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
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\StageController;
use App\Http\Controllers\Web\SupplierController;
use Illuminate\Support\Facades\Route;

// Raíz
Route::get('/', function () {
    return auth()->check() ? redirect('/orders') : redirect('/login');
});

// Auth — sin middleware
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Las rutas de login WebAuthn (webauthn.auth.options / webauthn.auth) ya las
// registra el propio paquete asbiin/laravel-webauthn (routes/routes.php) con
// el pipeline real (throttling + validación de assertion correctos) — antes
// había una redefinición aquí que colisionaba con esas mismas rutas/nombres
// y hacía que corriera un controlador propio en vez del paquete.

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

    // ── Rutas compartidas (admin + operativos) ──────────────────
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/orders', [OrderController::class, 'index']);

    // Ver detalle de orden — whereNumber evita capturar /create
    Route::get('/production-orders/{production_order}',
        [ProductionOrderController::class, 'show'])
        ->whereNumber('production_order');

    Route::get('/production-orders/calendar', [ProductionOrderController::class, 'calendar']);
    Route::get('/production-orders/calendar/{date}', [ProductionOrderController::class, 'dayDetail']);


    // Avanzar etapa — operativos y admin
    Route::post('/production-orders/{productionOrder}/advance-stage',
        [ProductionOrderController::class, 'advanceStage']);

    // API interna
    Route::get('/api/cities/{department}', function ($departmentId) {
        return cache()->remember("cities_{$departmentId}", 3600, fn () => App\Models\City::where('department_id', $departmentId)
                ->orderBy('name')->get(['id', 'name'])
        );
    });

    Route::get('/api/clients/search', [ClientController::class, 'search']);

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
        Route::post('/production-orders/{productionOrder}/cancel',
            [ProductionOrderController::class, 'cancel']);

        // Despacho / envío
        Route::post('/production-orders/{productionOrder}/dispatch',
            [ProductionOrderController::class, 'dispatch']);
        Route::post('/production-orders/{productionOrder}/guide-number',
            [ProductionOrderController::class, 'setGuideNumber']);
        Route::post('/production-orders/{productionOrder}/mark-sent',
            [ProductionOrderController::class, 'markSent']);
        Route::post('/production-orders/{productionOrder}/mark-delivered',
            [ProductionOrderController::class, 'markDelivered']);
        Route::post('/production-orders/{productionOrder}/mark-returned',
            [ProductionOrderController::class, 'markReturned']);
        Route::post('/production-orders/{productionOrder}/return-to-pending-dispatch',
            [ProductionOrderController::class, 'returnToPendingDispatch']);

        // Pagos
        Route::post('/payments', [PaymentController::class, 'store']);

        // Reportes Excel — exclusivo admin, verificado también en el
        // controller (abort_unless), no solo por este middleware.
        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/reports/financial', [ReportController::class, 'financialFull']);
        Route::get('/reports/operational', [ReportController::class, 'operationalFollowup']);
        Route::get('/reports/print-production-list', [ReportController::class, 'printProductionList']);

        // CRUDs
        Route::resource('/products', ProductController::class);
        Route::resource('/clients', ClientController::class);
        Route::resource('/stages', StageController::class);
        Route::resource('/users', App\Http\Controllers\Web\UserController::class);

        // Materiales y proveedores
        Route::resource('/suppliers', SupplierController::class);
        Route::resource('/materials', MaterialController::class);
        Route::resource('/material-purchases', MaterialPurchaseController::class)
            ->except(['edit', 'update', 'show']);
    });
});
