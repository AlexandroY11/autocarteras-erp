# Despachos y envío

## Solo Admin gestiona el despacho

Despachar (`dispatch`), registrar/corregir número de guía (`setGuideNumber`), marcar en tránsito (`markSent`), marcar entregado (`markDelivered`), y registrar devolución (`markReturned`) son acciones **exclusivas de Admin** — rutas bajo el middleware `admin`, resueltas por `App\Modules\Dispatch\Services\DispatchService`.

## No hay integración automática con Dropi (ni con ningún transportador)

El registro de guía y estados de despacho es **manual** — un Admin escribe el número de guía a mano, marca "en tránsito"/"entregado" a mano. No existe ningún webhook, API de transportadora, ni sincronización automática de estado de envío en el código actual.

## Cada despacho es una fila nueva, nunca se sobrescribe

`DispatchService::dispatch()` crea una fila nueva en `order_dispatches` (`$order->orderDispatches()->create([...])`) cada vez que una orden se despacha — incluyendo un redespacho después de una devolución. El historial completo de despachos de una orden queda intacto; `ProductionOrder::latestDispatch()` resuelve el más reciente por `dispatched_at`, pero las filas anteriores nunca se borran ni se modifican para "limpiar" el historial.

## Bloqueo por saldo de envío pendiente

`DispatchService::dispatch()` bloquea el despacho si `shipping_balance > 0`:
```php
if ($order->shipping_balance > 0) {
    throw new \Exception("No se puede despachar: falta cubrir el envío...", 422);
}
```

### Hallazgo pendiente de decisión — `shipping_price = NULL` se trata como 0 (gratis), no como "desconocido"

El código actual (`app/Services/PaymentAllocationService.php:17`) hace:
```php
$shippingPrice = (float) ($order->shipping_price ?? 0);
```
Es decir, **una orden con `shipping_price = NULL` tiene `shipping_balance = 0` siempre**, y por lo tanto **nunca queda bloqueada** por este chequeo — se trata como envío gratis, no como "dato desconocido que debería bloquear o alertar".

Esto es exactamente lo contrario de la intención de negocio esperada (que `NULL` signifique "desconocido", no "gratis"). Verificado en la BD de **desarrollo**: 11 de 14 órdenes tienen `shipping_price = NULL` — pero esta cifra **no es representativa de producción** (no hay acceso a la BD de producción desde este entorno). **Pendiente**: el usuario revisará la cifra real en producción antes de decidir si se corrige el código para que `NULL` bloquee el despacho (o alerte de otra forma) en vez de tratarse como cero.

## Qué queda sin definir

- Qué debe pasar exactamente cuando `shipping_price` es `NULL` (bloquear despacho, requerir que Admin lo complete a mano primero, otra cosa) — depende de la decisión pendiente arriba.
- No hay regla sobre reembolsos de envío si una orden se devuelve después de haber cobrado el envío.
