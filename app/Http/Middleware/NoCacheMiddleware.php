<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoCacheMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // headers->set() existe en cualquier Response de Symfony (incluida
        // BinaryFileResponse, la que usan las descargas de archivo) — a
        // diferencia de withHeaders(), que solo existe en Illuminate\Http\Response
        // y revienta con "Call to undefined method" en una descarga.
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }
}