<?php

namespace App\Services\Webauthn;

class WebauthnService
{
    /**
     * Normaliza la respuesta para el frontend WebAuthn JS (vendor).
     */
    public static function formatPublicKey(array $publicKey): array
    {
        // challenge SIEMPRE base64url string plano
        if (isset($publicKey['challenge'])) {
            $publicKey['challenge'] = self::toBase64Url($publicKey['challenge']);
        }

        // rpId seguro
        $publicKey['rpId'] = $publicKey['rpId'] ?? parse_url(config('app.url'), PHP_URL_HOST);

        // allowCredentials puede venir null o vacío → arreglarlo
        if (empty($publicKey['allowCredentials'])) {
            $publicKey['allowCredentials'] = [];
        } else {
            $publicKey['allowCredentials'] = array_map(function ($cred) {
                return [
                    'type' => 'public-key',
                    'id' => $cred['id'] ?? $cred['credentialId'] ?? null,
                    'transports' => $cred['transports'] ?? [],
                ];
            }, $publicKey['allowCredentials']);
        }

        return ['publicKey' => $publicKey];
    }

    private static function toBase64Url(string $value): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($value));
    }
}