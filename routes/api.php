<?php

use App\Http\Controllers\Api\WhatsappCatalogController;
use App\Http\Controllers\Api\WhatsappOrderController;
use App\Models\Holiday;
use App\Modules\Clients\Controllers\ClientController;
use App\Modules\Products\Controllers\ProductController;
use App\Services\BusinessDaysService;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {

    Route::middleware(['auth:sanctum', 'ability:products:read'])->group(function () {
        Route::get('products/search', [ProductController::class, 'search']);
    });
    Route::middleware(['auth:sanctum', 'ability:clients:read'])->group(function () {
        Route::get('clients/search',  [ClientController::class, 'search']);
    });

    // Bot de WhatsApp/n8n — token con abilities explícitas y mínimas
    // (whatsapp:catalog:read, whatsapp:orders:create), emitido vía el
    // comando `whatsapp:provision-token`, nunca por /auth/login. No puede
    // alcanzar ningún otro endpoint ability-gateado de esta API.
    Route::prefix('whatsapp')->middleware('auth:sanctum')->group(function () {
        Route::middleware('ability:whatsapp:catalog:read')->group(function () {
            Route::get('catalog', [WhatsappCatalogController::class, 'products']);
            Route::get('locations', [WhatsappCatalogController::class, 'locations']);
        });
        Route::middleware('ability:whatsapp:orders:create')->group(function () {
            Route::post('orders', [WhatsappOrderController::class, 'store']);
        });
    });

    require base_path('app/Modules/Auth/Routes/api.php');
    require base_path('app/Modules/Products/Routes/api.php');
    require base_path('app/Modules/Clients/Routes/api.php');
    require base_path('app/Modules/Production/Routes/api.php');
    require base_path('app/Modules/Payments/Routes/api.php');
    require base_path('app/Modules/Stages/Routes/api.php');
    require base_path('app/Modules/Dispatch/Routes/api.php');
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/holidays/{year}', function ($year) {
        return response()->json(
            Holiday::where('year', $year)
                ->orderBy('date')
                ->get(['date'])
                ->pluck('date')
        );
    });

    Route::get('utils/due-date', function () {
        $service = app(BusinessDaysService::class);
        $days    = request('days', 15);
        $dueDate = $service->calculateDueDate((int) $days);

        return response()->json([
            'due_date'           => $dueDate->toDateString(),
            'due_date_formatted' => $dueDate->format('d/m/Y'),
            'business_days'      => $days,
        ]);
    });
});