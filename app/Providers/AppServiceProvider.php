<?php

namespace App\Providers;

use App\Models\User;
use App\Models\WebauthnKey;
use App\Services\BusinessDaysService;
use App\Services\PaymentAllocationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use LaravelWebauthn\Facades\Webauthn;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BusinessDaysService::class);
        $this->app->singleton(PaymentAllocationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(\LaravelWebauthn\Models\WebauthnKey::class, \App\Models\WebauthnKey::class);

        // El paquete nunca llama a setSecuredRelyingPartyId() en su binding de
        // CeremonyStepManagerFactory (web-auth/webauthn-lib), asi que exige HTTPS
        // sin excepcion, incluso para localhost. Fuera de producción permitimos
        // localhost por HTTP para poder probar registro/login reales sin TLS —
        // en producción ya se sirve por HTTPS real, así que no hace falta y no
        // se debilita ningún control de seguridad ahí.
        $this->app->extend(CeremonyStepManagerFactory::class, function (CeremonyStepManagerFactory $factory) {
            if (! $this->app->environment('production')) {
                $factory->setSecuredRelyingPartyId(['localhost']);
            }

            return $factory;
        });

        // El paquete define el contrato LockoutResponse y trae su implementación
        // (respuesta 429 al bloquear por intentos fallidos), pero su propio
        // WebauthnServiceProvider nunca los conecta — sin este binding, llegar al
        // límite de intentos revienta con BindingResolutionException en vez de
        // devolver el 429 esperado.
        $this->app->bind(\LaravelWebauthn\Contracts\LockoutResponse::class, \LaravelWebauthn\Http\Responses\LockoutResponse::class);

        // Login por contraseña (web y API) — mismo criterio de bloqueo que ya
        // usa WebAuthn abajo (5 intentos/minuto por email+IP), pero antes no
        // tenía ningún throttle: Auth::attempt() se podía scriptear sin
        // límite contra /login y /api/v1/auth/login.
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('email')).'|'.$request->ip()));

        // Con config('webauthn.limiters.login') = 'webauthn-login', las rutas del
        // paquete usan el middleware throttle: estandar de Laravel en vez de meter
        // EnsureLoginIsNotThrottled en el pipeline — evita un TypeError real del
        // paquete (su AuthenticateController::store() esta tipado a devolver
        // LoginSuccessResponse, pero esa clase interna devuelve LockoutResponse al
        // bloquear, una interfaz hermana incompatible). Mismo criterio de bloqueo
        // (5 intentos/minuto por email+IP) que el paquete traia por defecto.
        RateLimiter::for('webauthn-login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('email')).'|'.$request->ip()));

        // El pipeline por defecto del paquete (AttemptToAuthenticate::attemptLogin)
        // asume que ya existe una sesión autenticada antes de verificar el segundo
        // factor. En este login WebAuthn "passwordless" nunca hay sesión previa, así
        // que resolvemos el usuario a partir del credentialId (base64url) y dejamos
        // que el propio paquete verifique la firma con Webauthn::validateAssertion().
        Webauthn::authenticateUsing(function (Request $request) {
            $rawId = $request->input('id');

            if (! is_string($rawId)) {
                return null;
            }

            $decoded = Base64UrlSafe::decode($rawId);

            $key = WebauthnKey::where(
                fn ($query) => $query->where('credentialId', Base64UrlSafe::encode($decoded))
                    ->orWhere('credentialId', Base64UrlSafe::encodeUnpadded($decoded))
            )->first();

            if ($key === null) {
                return null;
            }

            $user = User::find($key->user_id);

            if ($user === null) {
                return null;
            }

            $credentials = $request->only(['id', 'rawId', 'response', 'type']);

            return Webauthn::validateAssertion($user, $credentials) ? $user : null;
        });
    }
}
