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
        Log::info('Webauthn Login: Inicio de petición', ['payload_id' => $request->id]);

        // 1. Normalizar el ID (El navegador envía -_ y la DB tiene +/)
        $idFromBrowser = $request->id;
        $normalizedId = str_replace(['-', '_'], ['+', '/'], $idFromBrowser);
        
        // Asegurar que tenga el relleno == para la búsqueda
        if (!str_ends_with($normalizedId, '==')) {
            $normalizedId .= '==';
        }

        Log::info('Webauthn Login: ID Normalizado para búsqueda', ['normalized_id' => $normalizedId]);

        // 2. Buscar la llave
        $key = WebauthnKey::where('credentialId', $normalizedId)->first();

        if (!$key) {
            Log::warning('Webauthn Login: Llave no encontrada en la base de datos', ['buscado' => $normalizedId]);
            return response()->json(['message' => 'Usuario o llave no encontrados'], 422);
        }

        if (!$key->user) {
            Log::warning('Webauthn Login: Llave encontrada pero no tiene usuario asociado', ['key_id' => $key->id]);
            return response()->json(['message' => 'Usuario o llave no encontrados'], 422);
        }

        Log::info('Webauthn Login: Llave y usuario encontrados', ['user_id' => $key->user_id]);

        try {
            // 3. Validar la firma
            if (\LaravelWebauthn\Services\Webauthn::validateAssertion($key->user, $request->all())) {
                Log::info('Webauthn Login: Firma validada correctamente');

                Auth::login($key->user);
                
                return response()->json([
                    'status' => 'ok',
                    'redirect' => '/dashboard'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Webauthn Login: Excepción durante validateAssertion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Error interno: ' . $e->getMessage()], 500);
        }

        Log::error('Webauthn Login: validateAssertion retornó false (Firma inválida)');
        return response()->json(['message' => 'Falla en la firma biométrica'], 403);
    }
}
