<?php

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

    Route::post('orders/whatsapp', [WhatsappOrderController::class, 'store']);

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