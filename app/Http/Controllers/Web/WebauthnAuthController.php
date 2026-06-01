<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use LaravelWebauthn\Services\Webauthn;
use LaravelWebauthn\Models\WebauthnKey;
use App\Services\Webauthn\WebauthnService;
use LaravelWebauthn\Actions\LoginUserRetrieval;
use LaravelWebauthn\Actions\PrepareAssertionData;

class WebauthnAuthController extends Controller
{
    public function options(Request $request)
    {
        // Recuperar usuario y generar challenge
        $user = app(LoginUserRetrieval::class)->handle($request);
        $assertion = app(PrepareAssertionData::class)->handle($user);

        return response()->json(
            WebauthnService::formatPublicKey($assertion->publicKey)
        );
    }

    public function login(Request $request, Webauthn $webauthn)
    {
        $key = WebauthnKey::where('credentialId', $request->id)->first();

        if (!$key) {
            return response()->json(['message' => 'Llave no encontrada'], 422);
        }

        // En v5.5 el método es validateAssertion
        // Requiere la llave, el usuario y el array de datos
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
