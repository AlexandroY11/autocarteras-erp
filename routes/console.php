<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar tarea para sincronizar festivos de Colombia
// Se ejecuta el 1 de diciembre de cada año a las 1:00 AM (para tener listos los festivos del año siguiente)
Schedule::command('holidays:colombia --year=' . (now()->year + 1))
    ->cron('0 1 1 1 *') // 1 de enero a las 1:00 AM
    ->appendOutputTo(storage_path('logs/holidays.log'))
    ->withoutOverlapping();

// También asegurar festivos del año actual diariamente (por si acaso)
Schedule::command('holidays:colombia')
    ->dailyAt('00:05')
    ->timezone('America/Bogota')
    ->appendOutputTo(storage_path('logs/holidays.log'));