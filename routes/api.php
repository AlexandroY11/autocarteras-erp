<?php

use App\Models\Holiday;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {
    require base_path('app/Modules/Auth/Routes/api.php');
    require base_path('app/Modules/Products/Routes/api.php');
    require base_path('app/Modules/Clients/Routes/api.php');
    require base_path('app/Modules/Production/Routes/api.php');
    require base_path('app/Modules/Payments/Routes/api.php');
    require base_path('app/Modules/Stages/Routes/api.php');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/holidays/{year}', function($year) {
        return response()->json(
            Holiday::where('year', $year)
                ->orderBy('date')
                ->get(['date']) // Solo necesitamos la fecha para el cálculo
                ->pluck('date') // Esto devuelve directamente ["2026-06-08", "2026-06-15"]
        );
    });
});