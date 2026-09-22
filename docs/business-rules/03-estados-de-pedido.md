# Estados de pedido

## Dos columnas separadas, no un solo campo de estado

`production_orders` tiene **dos** columnas de estado independientes, con sus propios CHECK constraints en Postgres:

- **`status`** — progreso de producción: `pending`, `in_progress`, `done`, `cancelled`. Constraint real: `CHECK (status IN ('pending','in_progress','done','cancelled'))`.
- **`dispatch_status`** — progreso de despacho, solo tiene sentido una vez `status = done`: `NULL`, `pending_dispatch`, `dispatched`, `sent`, `delivered`, `returned`. Constraint real: `CHECK (dispatch_status IS NULL OR dispatch_status IN ('pending_dispatch','dispatched','sent','delivered','returned'))`.

**`status = 'done'` es un valor real y explícito de la columna** — no es algo inferido solo de `order_stages.completed_at`. `order_stages.completed_at` sí registra cuándo se completó cada etapa individual (trazabilidad histórica), pero el estado agregado del pedido vive en `production_orders.status`.

## Ciclo de vida completo

```
pending → in_progress → done → (dispatch_status) pending_dispatch → dispatched → sent → delivered
```

- **`cancelled` solo es posible hasta `pending_dispatch`** — nunca después de que la orden fue realmente despachada. Verificado en `ProductionOrderController::cancel()`: `if (! in_array($order->dispatch_status, [null, 'pending_dispatch'], true)) { /* bloqueado */ }`. Una orden puede cancelarse durante producción (`dispatch_status` aún `null`) o después de terminar producción pero antes de despachar (`dispatch_status = pending_dispatch`).
- **`returned` NO es terminal.** Puede volver a `pending_dispatch` explícitamente vía `ProductionOrderController::returnToPendingDispatch()`, y desde ahí generar un despacho nuevo (`dispatched`) — conservando el historial completo: cada despacho (incluido el devuelto) queda como su propia fila en `order_dispatches`, nunca se sobrescribe.

## Qué queda sin definir

- No hay un límite documentado de cuántas veces una orden puede devolverse y volver a despacharse.
- No hay una regla explícita sobre qué pasa con el `collected_amount` (recaudo) de un despacho devuelto — si se debe revertir contablemente o no. Ver el hallazgo de política de reversión de pagos en `06-eliminacion-y-cancelacion.md`.
