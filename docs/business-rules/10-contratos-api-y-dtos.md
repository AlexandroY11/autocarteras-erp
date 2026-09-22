# Contratos API y DTOs

## Contrato financiero: actual vs. antiguo — transicional, no completamente migrado

**Contrato actual** (el que debe usarse en código nuevo): `shipping_price`, `shipping_paid`, `shipping_balance`, `product_price`, `product_paid`, `product_balance` — separa explícitamente envío de producto, reflejando la distribución de pagos real (ver `05-pagos-y-financiero.md`).

**Contrato antiguo, deprecado**: `price`, `total_paid`, `balance`, `is_paid` — un solo número agregado, sin separar envío de producto.

### Hallazgo — el contrato antiguo sigue vivo hoy, mezclado con el actual, en la misma respuesta

`PaymentService::summary()` (`app/Modules/Payments/Services/PaymentService.php:54-64`) devuelve **ambos** en el mismo array: `price`, `shipping_price`, `total_paid`, `shipping_paid`, `shipping_balance`, `product_paid`, `product_balance`, `total_balance`, `is_paid`. No están separados ni uno reemplazó al otro. Esto es **transicional, según lo pedido en la sección 25 original** — no tratarlo como error a corregir de inmediato. Antes de remover `price`/`total_paid`/`is_paid` de esa respuesta, **hay que auditar quién consume esos 3 campos específicos** (frontend propio, integraciones externas) y documentar/migrar esos consumidores primero — nunca quitarlos en silencio.

## Client usa relaciones, no texto libre

`Client` debe usar `department_id`/`city_id` (FKs reales a `departments`/`cities`), nunca texto libre para ubicación — confirmado por las relaciones `department()`/`city()` en el modelo y su uso consistente en formularios/reportes.

## DTOs de `ProductionOrder`

### Hallazgo — riesgo real, pendiente de decisión de negocio: no existe un DTO de actualización separado

Solo existe `App\Modules\Production\DTOs\ProductionOrderDTO` (`app/Modules/Production/DTOs/ProductionOrderDTO.php`) — **no hay ningún `ProductionOrderUpdateDTO`**. El mismo DTO, con los mismos campos (`client_id`, `product_id`, `color`, `sticker`, `sticker_color`, `observations`, `price`, `due_date`), se usa tanto para crear como para actualizar.

`ProductionOrderController::update()` valida y pasa **exactamente esos mismos campos**, lo que significa que **hoy sí es posible modificar `client_id`, `product_id`, `price` y `due_date` de un pedido que ya podría tener pagos registrados** — esto rompe el principio de snapshot (ver `05-pagos-y-financiero.md`/`09-creacion-de-pedidos.md`): un pago ya se calculó y distribuyó contra un `price`/`shipping_price` que podría cambiar después de registrado el pago, sin que exista ningún recálculo ni bloqueo.

Lo que sí está protegido en la práctica (aunque no por un DTO separado, sino porque el formulario/controlador nunca los lee del request): `status`, `current_stage_id`, `consecutive`, `shipping_price`.

**Acceso confirmado**: `PUT /production-orders/{production_order}` (el método `update()` que permite esta edición) vive bajo el mismo `Route::middleware('admin')` que `create()`/`store()` en `routes/web.php` — **exclusivo de Admin**, igual que la creación (ver `09-creacion-de-pedidos.md`). Esto acota el riesgo real: no es una vía abierta a cualquier rol, sino a un actor de confianza (Admin) que podría cometer un error, no a cualquier usuario del sistema.

**Se investigó si esto se ha usado alguna vez en un pedido con pagos ya registrados** (comparando `updated_at` de la orden contra `created_at` de sus pagos) — **no fue posible determinarlo**: no existe tabla de auditoría en el proyecto, y `updated_at` se actualiza igual por acciones rutinarias no relacionadas (avanzar etapa, cambiar `dispatch_status`, cancelar), por lo que no hay forma de aislar si alguna vez se usó específicamente esta vía de edición sobre un pedido con pagos. **No asumir que nunca pasó ni que sí pasó** — es un riesgo real y abierto, pendiente de que el negocio decida si se separa el DTO de actualización y se restringen esos 4 campos, o si se acepta el riesgo documentándolo.

## Regla de proceso: cambiar un contrato de API implica auditar consumidores primero

Antes de cambiar el nombre de un campo de una respuesta API existente:
1. Buscar todos los consumidores reales (frontend propio, integraciones externas conocidas).
2. Documentar el cambio.
3. Actualizar los consumidores.

**Nunca cambiar nombres de campos en silencio** — ni siquiera si el nombre nuevo es "más correcto".

## Qué queda sin definir

- No hay lista formal de qué integraciones externas (aparte de n8n) consumen `PaymentService::summary()` hoy — hay que preguntar antes de asumir que es seguro quitar los campos antiguos.
- No hay decisión tomada sobre el hallazgo del DTO de actualización — ver arriba.
