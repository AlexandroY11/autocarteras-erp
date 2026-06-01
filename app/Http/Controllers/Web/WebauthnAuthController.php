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
        try {
            // 1. Limpiar el ID que viene del navegador (remover caracteres URL-safe y relleno)
            $credentialId = $request->id;

            // 2. Buscar la llave usando comparaciones flexibles para Base64
            $key = WebauthnKey::where('credentialId', $credentialId)
                ->orWhere('credentialId', $credentialId . '==')
                ->orWhere('credentialId', $credentialId . '=')
                ->first();

            if (!$key) {
                return response()->json(['message' => 'Dispositivo no reconocido (ID mismatch)'], 422);
            }

            // 3. Validar la aserción (firma criptográfica)
            // Pasamos la llave, el objeto usuario y los datos crudos
            if ($webauthn->validateAssertion($key, $key->user, $request->all())) {
                
                // 4. Login Manual
                Auth::loginUsingId($key->user_id);

                return response()->json([
                    'status' => 'ok',
                    'redirect' => '/dashboard'
                ]);
            }

            return response()->json(['message' => 'La firma de la huella es inválida'], 403);

        } catch (\Exception $e) {
            // Esto evita el 500 y te dice qué pasó realmente en el log
            \Log::error("WebAuthn Login Error: " . $e->getMessage());
            return response()->json([
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
