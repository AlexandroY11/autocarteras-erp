<?php

namespace App\Services\Webauthn;

class WebauthnService
{
    public static function formatPublicKey(array $publicKey): array
    {
        if (isset($publicKey['challenge'])) {
            $publicKey['challenge'] = self::toBase64Url($publicKey['challenge']);
        }

        $publicKey['rpId'] = $publicKey['rpId'] ?? parse_url(config('app.url'), PHP_URL_HOST);

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

    /**
     * Convierte el ID del navegador al formato con el que se guarda en DB (+ / y ==)
     */
    public static function fixCredentialId(string $id): string
    {
        $fixed = str_replace(['-', '_'], ['+', '/'], $id);
        $mod4 = strlen($fixed) % 4;
        if ($mod4 > 0) {
            $fixed .= substr('====', $mod4);
        }
        return $fixed;
    }

    private static function toBase64Url(string $value): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($value));
    }
}
