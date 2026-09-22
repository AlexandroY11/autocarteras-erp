# Pagos y financiero

## Fuente del catálogo y snapshot al crear el pedido

- `products.base_price` y `products.shipping_price` son la fuente del catálogo — el precio "de lista" de un producto.
- Al crear un pedido, esos valores se **copian (snapshot)** a `production_orders.price` y `production_orders.shipping_price` en ese momento. Si el precio del producto cambia después en el catálogo, los pedidos ya creados **no** se actualizan — conservan el precio que tenían al momento de crearse. Ver `09-creacion-de-pedidos.md`.

## Distribución de pagos: primero envío, luego producto

`PaymentAllocationService::breakdown()` recalcula la distribución completa cada vez, a partir de todos los pagos existentes de la orden — no hay un estado de asignación guardado en BD. Regla: **cada pago cubre primero el saldo de envío pendiente; el excedente se aplica al producto.**

**Ejemplo numérico:**
- Orden: `shipping_price = 20.000`, `price = 300.000` (total a pagar: 320.000).
- Pago 1: 50.000 → cubre los 20.000 de envío completos, el excedente (30.000) va a producto. Envío pagado: 20.000/20.000. Producto pagado: 30.000/300.000.
- Pago 2: 100.000 → envío ya está cubierto, los 100.000 completos van a producto. Producto pagado: 130.000/300.000.
- Saldo total pendiente: 320.000 − 150.000 = 170.000 (todo en producto, envío ya en 0).

## El recaudo de guía es el saldo del producto, no el de envío

`DispatchService::dispatch()`:
```php
$collectedAmount = $order->product_balance > 0 ? $order->product_balance : null;
```
El monto que se anota como "a cobrar contraentrega" en la guía es el **saldo de producto pendiente**, nunca el de envío — el envío ya debe estar cubierto para poder despachar (ver `04-despachos-y-envio.md`).

## Pago contra entrega (COD): automático e idempotente

`DispatchService::markDelivered()` — si al marcar una orden como entregada queda saldo de producto pendiente, se genera **automáticamente** un `Payment` con `type = 'cod'` por ese monto exacto. Es **idempotente**: verifica primero si ya existe un pago `cod` para ese despacho específico (`Payment::where('order_dispatch_id', $dispatch->id)->where('type', 'cod')->exists()`) antes de crear uno nuevo — marcar "entregado" dos veces (por error, doble clic, reintento) nunca duplica el cobro.

## Qué queda sin definir

- No hay política de qué pasa si el recaudo real contraentrega fue distinto al `collected_amount` calculado (ej. el cliente pagó de más o de menos en la puerta) — no asumir un mecanismo de ajuste, preguntar.
- Métodos de pago válidos hoy: `efectivo`, `nequi`, `nu` (agregado recientemente, ver historial de commits) — cualquier método nuevo requiere migración del CHECK constraint de `payments.payment_method`, no solo cambio de frontend.
