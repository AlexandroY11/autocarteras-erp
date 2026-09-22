# Producción y etapas

## Etapas reales (tabla `stages`), verificadas contra la BD

**Activas hoy:** Pendiente, Moldeado, Vaciado, Lijado, Pintura, Acabado, Finalizado (7).
**Inactivas:** Empaque, Enviado (2).

- **Enviado** — etapa histórica desactivada. Sigue presente en la BD porque hay pedidos antiguos con historial en `order_stages` que la referencian. `Stage::enviadoId()` resuelve su id buscando por nombre — nunca por un id numérico quemado a mano, porque el id depende de la secuencia de cada entorno (dev/test/producción) y no es estable entre ellos.
- **Empaque** — desactivada intencionalmente (confirmado: comentario explícito en la migración `2026_09_20_000001_add_finalizado_stage_and_auto_complete.php`: *"'Empaque' deja de ser una etapa de trabajo real, pero se conserva (hay pedidos con historial en order_stages que la referencian)"*). Su rol como último paso de trabajo fue consolidado en **Finalizado**.
- **Finalizado** — etapa terminal con `auto_complete = true`. Nadie la "trabaja": en cuanto una orden llega a ella, `ProductionOrderService::advanceStage()` la completa automáticamente en el mismo paso y pasa la orden a `status = done`, `dispatch_status = pending_dispatch`. Ningún trabajador la ve en su lista de habilidades para "avanzar" — se completa sola.

## Despacho/envío/entrega son estados del pedido, no etapas de producción

`dispatched`, `sent`, `delivered`, `returned` viven en la columna `production_orders.dispatch_status`, **no** en la tabla `stages`. Una vez que un pedido llega a `status = done`, ya no tiene `current_stage_id` (es `null`) — el seguimiento pasa a ser exclusivamente de despacho, ver `03-estados-de-pedido.md` y `04-despachos-y-envio.md`.

## Quién puede avanzar qué etapa

- Worker/Director solo pueden avanzar una orden si tienen la habilidad para la etapa **actual** de esa orden (tabla `user_skills`) — `User::canAdvanceStage(int $stageId)`.
- **La validación vive en el backend** (`ProductionOrderService::advanceStage()` lanza excepción si no tiene la habilidad), nunca solo en el frontend — el frontend oculta/deshabilita el gesto de swipe como ayuda visual, pero el backend es quien realmente bloquea.
- Admin puede avanzar cualquier etapa de cualquier orden, sin restricción de habilidad.

## Qué queda sin definir

- No hay una regla documentada sobre si una etapa puede "saltarse" manualmente (ej. de Moldeado directo a Pintura) — hoy `advanceStage()` siempre avanza a la siguiente etapa activa en orden (`ProductionOrder::nextStage()`), nunca a una arbitraria. Si se necesita saltar etapas, preguntar antes de asumir que es válido.
- No hay regla sobre qué pasa si se desactiva una etapa que tiene órdenes actualmente *en* ella (current_stage_id apuntando a una etapa recién desactivada) — no se ha dado el caso, no asumir el comportamiento.
