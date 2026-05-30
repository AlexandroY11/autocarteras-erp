<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {
    require base_path('app/Modules/Auth/Routes/api.php');
    require base_path('app/Modules/Products/Routes/api.php');
    require base_path('app/Modules/Clients/Routes/api.php');
    require base_path('app/Modules/Production/Routes/api.php');
    require base_path('app/Modules/Payments/Routes/api.php');
    require base_path('app/Modules/Stages/Routes/api.php');
});
