<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use LaravelWebauthn\Services\Webauthn;
use LaravelWebauthn\Models\WebauthnKey;

class WebauthnAuthController extends Controller
{

    public function login(Request $request, Webauthn $webauthn)
    {
        $id = $request->id;

        // Buscamos la llave probando el ID tal cual viene y también con el posible relleno '=='
        $key = WebauthnKey::where('credentialId', $id)
            ->orWhere('credentialId', $id . '==') 
            ->orWhere('credentialId', str_replace(['-', '_'], ['+', '/'], $id) . '==')
            ->first();

        if (!$key) {
            return response()->json(['message' => 'Llave no encontrada en DB'], 422);
        }

        if ($webauthn->validateAssertion($key, $key->user, $request->all())) {
            Auth::loginUsingId($key->user_id);

            return response()->json([
                'status' => 'ok',
                'redirect' => '/dashboard'
            ]);
        }

        return response()->json(['message' => 'Fallo de autenticación'], 403);
    }

}
