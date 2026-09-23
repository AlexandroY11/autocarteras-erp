<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Antes, desactivar un usuario (UserController::update()) no revocaba nada
 * en curso — 'active' solo se verificaba una vez, al momento del login
 * (AuthController::login()). Con sesión "recordarme" + tokens Sanctum sin
 * expiración, un usuario desactivado seguía teniendo acceso indefinido.
 * Este middleware revalida 'active' en cada request autenticado, sin
 * importar el guard (sesión web o token Sanctum).
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        // $request->user() sin argumento resuelve contra el guard 'web'
        // (default de config/auth.php) — en una request de API pura
        // (token Bearer, sin sesión) eso da null aunque el token sea
        // válido, así que se revisa también el guard 'sanctum' explícito.
        $user = $request->user() ?? $request->user('sanctum');

        if ($user && ! $user->active) {
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
            }

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            abort(403, 'Tu cuenta ha sido desactivada. Contacta a un administrador.');
        }

        return $next($request);
    }
}
