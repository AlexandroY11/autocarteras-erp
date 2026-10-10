# Despachos y envío

## Solo Admin gestiona el despacho

Despachar (`dispatch`), registrar/corregir número de guía (`setGuideNumber`), marcar en tránsito (`markSent`), marcar entregado (`markDelivered`), y registrar devolución (`markReturned`) son acciones **exclusivas de Admin** — rutas bajo el middleware `admin`, resueltas por `App\Modules\Dispatch\Services\DispatchService`.

## No hay integración automática con Dropi (ni con ningún transportador)

El registro de guía y estados de despacho es **manual** — un Admin escribe el número de guía a mano, marca "en tránsito"/"entregado" a mano. No existe ningún webhook, API de transportadora, ni sincronización automática de estado de envío en el código actual.

## Cada despacho es una fila nueva, nunca se sobrescribe

`DispatchService::dispatch()` crea una fila nueva en `order_dispatches` (`$order->orderDispatches()->create([...])`) cada vez que una orden se despacha — incluyendo un redespacho después de una devolución. El historial completo de despachos de una orden queda intacto; `ProductionOrder::latestDispatch()` resuelve el más reciente por `dispatched_at`, pero las filas anteriores nunca se borran ni se modifican para "limpiar" el historial.

## Saldo de envío pendiente: confirmación explícita, no bloqueo (regla 16)

**Cambió por decisión del dueño del negocio (2026-10-09).** Antes,
`DispatchService::dispatch()` bloqueaba el despacho de forma absoluta si
`shipping_balance > 0`. Ahora **se puede despachar con el envío sin pagar,
pero solo confirmando explícitamente**:

- Sin confirmación y con saldo de envío pendiente → `422` (mismo mecanismo
  de excepción con código HTTP que el resto del servicio).
- Con confirmación → el despacho procede y **queda constancia en la guía**:
  `order_dispatches.shipped_with_pending_shipping = true` y
  `pending_shipping_amount` con el snapshot del saldo en ese momento (no se
  recalcula después).
- La confirmación viaja como `confirm_unpaid_shipping` y **la exige el
  backend**, no el formulario: `DispatchService::dispatch()` rechaza el
  despacho si el flag no llega. En la web el flag lo acompaña el diálogo; la
  validación de servidor es lo que cubre a la API y a cualquier otro
  llamador que intente despachar sin confirmar.
- **Web y API usan la misma lógica**: los dos controllers solo validan el
  campo y lo pasan al servicio. La regla no está reimplementada en ninguno.

En la web, el formulario de despacho de `orders/show.blade.php` muestra el
aviso del saldo y pide confirmación con el diálogo *"¿Seguro que quieres
despachar sin que se haya pagado el envío?"*.

**Quién confirmó no tiene columna propia a propósito:** la confirmación y el
despacho son la misma acción, en el mismo request, del mismo Admin que ya
queda registrado en `dispatched_by`.

**Las filas de despacho anteriores no se tocan.** Cada despacho sigue siendo
una fila nueva (ver la sección de arriba), así que el historial de una orden
redespachada conserva cada intento con su propia bandera.

**El cobro posterior de ese envío no está definido todavía** — se decide en
la fase 3 (nómina). No asumir ningún mecanismo de cobro, recordatorio ni
descuento.

### Punto de entrada que **no** puede confirmar: el swipe de la lista

`orders/index.blade.php` tiene un segundo acceso al despacho (swipe sobre la
tarjeta, formulario oculto con solo `@csrf`, líneas ~157-199) que postea a la
misma ruta. Ese camino **no manda `confirm_unpaid_shipping`**, así que un
pedido con envío pendiente despachado por swipe recibe el `422` y el Admin
tiene que entrar al detalle del pedido para confirmar. No es una regresión
(antes ese swipe también fallaba, por el bloqueo duro), pero sí una
limitación conocida: el diálogo pedido por el dueño se implementó en el
detalle del pedido, no en el swipe.

## Bolsillo de envíos pendientes

Vista de **solo lectura, exclusiva de Admin** (`/orders/pending-shipping-wallet`,
botón "Bolsillo" en el header de Órdenes). Es una **consulta derivada: no hay
tabla ni estado propio.**

Qué lista: pedidos que **salieron despachados con el envío sin pagar** y
siguen debiéndolo. El filtro es por la bandera histórica del despacho
(`whereHas('orderDispatches', shipped_with_pending_shipping = true)`), **no
por `dispatch_status`** — decisión deliberada: un pedido devuelto que vuelve
a `pending_dispatch` con el envío sin pagar **debe seguir en el bolsillo**, y
filtrando por estado desaparecería. `whereHas()` es una prueba de
existencia, así que un pedido redespachado varias veces aparece una sola vez.

Exclusiones: pedidos cancelados y pedidos con `shipping_price = NULL` (estos
últimos por doble motivo — nunca reciben la bandera, porque su
`shipping_balance` siempre da 0, y además la consulta los filtra de forma
explícita).

Los devueltos se muestran con su etiqueta de estado
(`ProductionOrder::DISPATCH_STATUS_LABELS`, "Devuelto"), no con un
vocabulario nuevo.

**Sale del bolsillo solo.** `shipping_balance` se recalcula desde los pagos
en cada consulta (no hay estado guardado), así que registrar el pago del
envío basta para que el pedido desaparezca — no hay nada que marcar a mano.
Por lo mismo la vista no tiene ninguna acción: no hay "marcar pagado" ni
swipe. Eso se define en la fase 3.

El último filtro (`shipping_balance > 0`) se aplica en PHP porque
`shipping_balance` es un accessor calculado por `PaymentAllocationService`,
no una columna — replicarlo en SQL duplicaría la regla de "el envío se cubre
primero".

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
