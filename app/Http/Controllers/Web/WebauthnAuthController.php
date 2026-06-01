<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use LaravelWebauthn\Services\Webauthn;
use LaravelWebauthn\Models\WebauthnKey;
use App\Services\Webauthn\WebauthnService;
use App\Models\User;

class WebauthnAuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Normalizar ID usando el Service
        $fixedId = WebauthnService::fixCredentialId($request->id);

        // 2. Buscar llave y usuario
        $key = WebauthnKey::where('credentialId', $fixedId)->first();

        if (!$key || !($user = User::find($key->user_id))) {
            return response()->json(['message' => 'Dispositivo o usuario no reconocido'], 422);
        }

        // 3. Validar firma con la librería (pasando los 2 argumentos que recibe tu función)
        if (\LaravelWebauthn\Services\Webauthn::validateAssertion($user, $request->all())) {
            Auth::login($user);

            return response()->json([
                'status' => 'ok',
                'redirect' => '/dashboard'
            ]);
        }

        return response()->json(['message' => 'Falla en la firma biométrica'], 403);
    }
}
