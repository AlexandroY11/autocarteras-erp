<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use LaravelWebauthn\Services\Webauthn;
use LaravelWebauthn\Models\WebauthnKey;

class WebauthnAuthController extends Controller
{
    public function login(Request $request)
    {
        $key = WebauthnKey::where('credentialId', $request->id)
            ->orWhere('credentialId', $request->id . '==')
            ->first();

        if (!$key || !$key->user) {
            return response()->json(['message' => 'Usuario o llave no encontrados'], 422);
        }

        if (\LaravelWebauthn\Services\Webauthn::validateAssertion($key->user, $request->all())) {
            
            Auth::login($key->user);

            return response()->json([
                'status' => 'ok',
                'redirect' => '/dashboard'
            ]);
        }

        return response()->json(['message' => 'Falla en la firma biométrica'], 403);
    }

}
