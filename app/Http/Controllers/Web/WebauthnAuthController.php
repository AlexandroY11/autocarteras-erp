<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use LaravelWebauthn\Services\Webauthn;
use LaravelWebauthn\Models\WebauthnKey;

class WebauthnAuthController extends Controller
{
    public function login(Request $request)
    {
        $idFromBrowser = $request->id;
        $idWithPadding = str_ends_with($idFromBrowser, '==') ? $idFromBrowser : $idFromBrowser . '==';

        // 1. Buscamos la llave
        $key = WebauthnKey::where('credentialId', $idWithPadding)->first();

        if (!$key) {
            return response()->json(['message' => 'Llave no encontrada'], 422);
        }

        // 2. BUSQUEDA MANUAL DEL USUARIO (Aquí estaba el fallo)
        $user = \App\Models\User::find($key->user_id);

        if (!$user) {
            Log::error('Webauthn: La llave existe pero el usuario ID ' . $key->user_id . ' no.');
            return response()->json(['message' => 'Usuario no vinculado'], 422);
        }

        // 3. Validar con el objeto $user que acabamos de encontrar
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
