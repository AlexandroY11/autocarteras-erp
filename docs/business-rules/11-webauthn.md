# WebAuthn / Passkeys

## La funcionalidad debe conservarse

El login por huella/FaceID (WebAuthn/passkeys) es una funcionalidad real del producto, construida sobre el paquete `asbiin/laravel-webauthn`. **No debe eliminarse ni degradarse** sin aprobación explícita — cualquier cambio en este flujo debe verificarse con una prueba real de login por passkey de punta a punta antes de darse por terminado (no basta con revisar el código).

## Resolución del usuario: SÍ requiere conversión manual de base64url (corregido — el código gana)

`app/Providers/AppServiceProvider.php` implementa `Webauthn::authenticateUsing()` para resolver el usuario en un login passwordless (sin sesión previa):

```php
$rawId = $request->input('id');
$decoded = Base64UrlSafe::decode($rawId);
$key = WebauthnKey::where(
    fn ($query) => $query->where('credentialId', Base64UrlSafe::encode($decoded))
        ->orWhere('credentialId', Base64UrlSafe::encodeUnpadded($decoded))
)->first();
```

Esto decodifica `$request->id` y lo vuelve a codificar en **2 formatos posibles** (con y sin padding) para compararlo contra `credentialId` — porque comparar el string crudo de `$request->id` directamente contra `credentialId` sin normalizar el padding **no es confiable**: pueden no coincidir por diferencias de padding aunque representen el mismo credential id. Esta conversión manual es intencional y necesaria, **verificada con una ceremonia WebAuthn real de punta a punta** (registro de passkey + login exclusivamente con passkey, sin contraseña, usando un autenticador virtual real vía Chrome DevTools Protocol).

## Protección contra intentos excesivos

El wiring por defecto del paquete `asbiin/laravel-webauthn` tenía 2 problemas reales que se corrigieron en `AppServiceProvider::boot()`:
- Faltaba el binding de `LockoutResponse` — sin él, llegar al límite de intentos fallidos producía un `BindingResolutionException` en vez de un `429`.
- El rate limiter por defecto del paquete causaba un `TypeError` real al bloquear (su `AuthenticateController::store()` está tipado a devolver `LoginSuccessResponse`, pero al bloquear devuelve `LockoutResponse`, una interfaz hermana incompatible).

Se registró un rate limiter dedicado (`webauthn-login`): **5 intentos por minuto**, por combinación de email + IP.

## Qué queda sin definir

- No hay una política de expiración/rotación de llaves registradas (`webauthn_keys`) — no asumir que existe un límite de dispositivos ni una fecha de expiración.
- El registro de una nueva llave (`webauthn.create`/`webauthn.store`) requiere una sesión ya autenticada por contraseña — no hay forma hoy de registrar la primera llave sin haber iniciado sesión al menos una vez de forma tradicional.
