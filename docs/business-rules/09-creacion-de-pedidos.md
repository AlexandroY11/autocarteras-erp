# Creación de pedidos

## Acceso

Crear un pedido (`GET /production-orders/create`, `POST /production-orders`) es **exclusivo de Admin** — ambas rutas viven bajo `Route::middleware('admin')` en `routes/web.php`. Director y Worker no tienen ninguna vía en el panel web para crear un pedido. (Existe además un endpoint API separado, `POST /api/orders/whatsapp`, para creación vía integración — protegido por abilities de Sanctum, no por rol humano; ver `01-roles-y-permisos.md` y `10-contratos-api-y-dtos.md`.)

## Qué calcula/snapshotea el backend al crear un pedido

- **`shipping_price`** — se recalcula server-side desde el catálogo (`Product::find($dto->product_id)?->shipping_price`), **ignora cualquier valor que llegue del request**. Snapshot fijo desde la creación (ver `05-pagos-y-financiero.md`).
- **`current_stage_id`** — se asigna la primera etapa activa configurada (`Stage::where('active', true)->orderBy('order')->first()`), nunca viene del cliente.
- **`status`** — siempre `'pending'` al crear, nunca viene del cliente.
- **`created_by`** — el usuario autenticado que crea el pedido, nunca viene del cliente.
- **`consecutive`** — número correlativo generado por el backend (ver hallazgo abajo sobre cómo, exactamente).

## Hallazgo — pendiente de decisión de negocio: `price` y `due_date` se aceptan directamente del formulario, sin recalcular server-side

`ProductionOrderController::store()` valida `'price' => 'required|numeric|min:0'` y `'due_date' => 'required|date'` — solo verifica que sean un número/fecha válidos, **no que coincidan con `product.base_price` ni con el cálculo de `BusinessDaysService`**. Ambos se pasan tal cual al DTO y se guardan. Confirmado en el formulario (`orders/form.blade.php:346`): el campo de fecha es un `<input type="date">` genuinamente editable (`x-model="dueDate"`), con un texto de ayuda ("Calculada: 15 días hábiles") que es solo una sugerencia visual, no un valor forzado por el servidor. El campo de precio se comporta igual: se pre-llena en el frontend con el `base_price` del producto seleccionado, pero el valor final que llega al servidor es el que haya quedado en el input, editable.

**No está corregido ni declarado bug todavía** — el alcance del riesgo es acotado porque esta vía es exclusiva de Admin (ver "Acceso" arriba), no de cualquier rol. Queda pendiente de que el negocio decida: (a) es una función real y deseada (permitir que Admin ajuste precio/fecha manualmente en casos especiales, ej. un descuento o una fecha urgente), y entonces se documenta como regla intencional, o (b) debe bloquearse para que el precio sea siempre el de catálogo y la fecha siempre la calculada, igual que ya ocurre con `shipping_price`.

## Consecutivo — mecanismo real vs. infraestructura sin usar

`ProductionOrderService::create()` calcula el consecutivo en PHP: `(ProductionOrder::withTrashed()->max('consecutive') ?? 0) + 1`, y lo pasa explícito al `INSERT`. Existe una secuencia real de Postgres (`production_orders_consecutive_seq`, migración `2026_09_18_000014`) puesta como `DEFAULT` de la columna — pero al especificarse el valor explícitamente en el `INSERT`, Postgres nunca llega a usar ese `DEFAULT`. Ver el hallazgo de riesgo completo, con el resultado de la consulta de duplicados, en `13-workflow-y-seguridad.md`.

## Qué queda sin definir

- Si se decide que `price`/`due_date` deben bloquearse (opción b arriba), falta definir si el formulario deja de mostrar esos campos como editables o si pasan a ser de solo lectura visualmente pero el backend los ignora de todas formas.
- No hay regla sobre si un pedido creado vía WhatsApp/n8n debería tener las mismas restricciones de precio/fecha que el panel web — hoy `WhatsappOrderController` calcula su propia fecha (`BusinessDaysService::calculateDueDate(15)`) si no llega una en el request, pero si llega, también la acepta tal cual — mismo patrón, mismo hallazgo pendiente.
