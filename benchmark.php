<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$start = microtime(true);
$products = \App\Models\Product::all();
echo 'Products query: ' . round((microtime(true) - $start) * 1000) . 'ms' . PHP_EOL;

$start = microtime(true);
$users = \App\Models\User::all();
echo 'Users query: ' . round((microtime(true) - $start) * 1000) . 'ms' . PHP_EOL;

$start = microtime(true);
$clients = \App\Models\Client::all();
echo 'Clients query: ' . round((microtime(true) - $start) * 1000) . 'ms' . PHP_EOL;

// Test de conexión a Redis
$start = microtime(true);
\Illuminate\Support\Facades\Cache::put('test', 'ok', 10);
$val = \Illuminate\Support\Facades\Cache::get('test');
echo 'Redis: ' . round((microtime(true) - $start) * 1000) . 'ms — valor: ' . $val . PHP_EOL;