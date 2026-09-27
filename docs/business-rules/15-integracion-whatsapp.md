# Integración WhatsApp/n8n

## Qué es

Carlos (Director) y Jhon (Admin) crean pedidos escribiéndole 1:1 a un bot de
WhatsApp. Un agente de IA en **n8n** sostiene toda la conversación, infiere
producto/color aunque el cliente escriba mal, arma el resumen de
confirmación y solo **entonces** hace una única llamada a esta API con el
payload ya resuelto. n8n y la orquestación conversacional están fuera de
este repo — aquí solo vive la API que recibe la llamada final.

## Endpoints

- `GET /api/v1/whatsapp/catalog` — productos activos (`id`, `name`,
  `base_price`, `shipping_price`), para que la IA nunca invente un producto
  y arme el resumen con precio real.
- `GET /api/v1/whatsapp/locations` — todos los departamentos y ciudades
  (`id`, `name`, y `department_id` en cada ciudad), para que la IA resuelva
  el texto libre del cliente a IDs válidos antes de llamar al endpoint de
  creación.
- `POST /api/v1/whatsapp/orders` — creación del pedido. Ver contrato abajo.

Las 3 rutas viven bajo `auth:sanctum` + una ability específica cada grupo
(`whatsapp:catalog:read` para las 2 de lectura, `whatsapp:orders:create`
para la de escritura) — el token del bot no tiene ninguna otra ability, así
que no puede alcanzar `/clients/search`, `/production-orders`, ni ningún
endpoint financiero, aunque comparta el mismo guard `auth:sanctum`.

## Contrato de `POST /api/v1/whatsapp/orders`

**Obligatorios:** `idempotency_key`, `client_first_name`, `client_last_name`,
`client_phone`, `city_id`, `department_id`, `address`, `product_id`,
`cart_color`. Cualquiera ausente rechaza con `422` y mensaje específico por
campo.

**Opcionales:** `client_email`, `sticker` (boolean), `sticker_color` (texto
libre), `advance_amount`.

**Nunca se acepta `price` ni `shipping_price` del payload** — si vinieran,
se ignoran. Ambos siempre son el snapshot del catálogo en el momento de
crear (mismo mecanismo que ya usa `ProductionOrderService::create()` para
el resto del ERP — no se duplicó lógica).

**`due_date`** siempre se calcula con `BusinessDaysService::calculateDueDate(15)`
— no se acepta del payload.

**`product_id`** debe existir **y estar activo** — si no,
`"El producto no existe o ya no está disponible."`.

**`cart_color`** debe existir en `cart_colors` con `active = true` — hoy
son exactamente **Gris** y **Negro** (`database/migrations/2026_09_18_000010_create_cart_colors_table.php`).
Si no, `"Color no disponible — colores válidos: Gris, Negro."`. **Nota:**
esto es más estricto que el formulario web de creación de pedidos
(`orders/form.blade.php`), que todavía ofrece "Beige" como tercera opción
hardcodeada aunque esa tabla nunca la tuvo — ver hallazgo ya documentado en
`08-catalogo-y-colores.md`. No se tocó esa inconsistencia aquí.

## Resolución de cliente por teléfono

- Si el teléfono no existe: se crea un cliente nuevo con los datos
  recibidos.
- Si ya existe: **se rellenan solo los campos que hoy están vacíos**
  (`first_name`, `last_name`, `email`, `city_id`, `department_id`,
  `address`) — nunca se sobrescribe un dato ya presente. Decisión explícita
  del usuario: evitar que un error de la IA borre un dato bueno que ya
  estaba correcto.
- Si la `address` recibida es **distinta** a la ya guardada, no se toca el
  cliente — se guarda una nota en las `observations` del pedido nuevo
  (`"Dirección distinta reportada por WhatsApp: ... Dirección registrada
  del cliente sin cambios: ..."`) para que un humano lo revise. No se
  resuelve el conflicto solo.
- Condición de carrera de `phone` (dos conversaciones nuevas casi
  simultáneas con el mismo teléfono): `clients.phone` ya tiene constraint
  única a nivel de BD; si el `INSERT` choca, se recupera el cliente ya
  creado por la otra request en vez de fallar.

## Idempotencia

`production_orders.idempotency_key` — `string`, `nullable`, **`unique`** a
nivel de BD. Flujo:
1. Chequeo rápido: si ya existe una orden con esa key, se devuelve esa
   misma orden con `200` y `"idempotent_replay": true` — sin tocar nada
   más. Cubre el caso normal (reintento de n8n tras un timeout).
2. Si dos llamadas verdaderamente simultáneas pasan el chequeo anterior al
   mismo tiempo, la constraint única del `INSERT` hace fallar a la segunda
   dentro de la transacción; se captura `UniqueConstraintViolationException`
   y se re-consulta por `idempotency_key` (fuera de la transacción ya
   revertida — necesario en Postgres, donde una transacción con un
   statement fallido queda inservible hasta el rollback) para devolver la
   orden creada por la primera, también con `200`.
3. **Solo la creación exitosa nueva devuelve `201`** — cualquier replay
   (detectado en el paso 1 o recuperado en el paso 2) devuelve `200`.

## `production_orders.origin`

Columna `string`, default `'manual'`, valor `'whatsapp'` para todo lo
creado por este flujo — mismo patrón que ya usan `status`/`dispatch_status`
en este proyecto (string + validación de aplicación, sin `enum` nativo ni
`CHECK` de Postgres). Sirve para reportes/filtros futuros sin depender
solo de `created_by` apuntando al usuario `integration`.

## Reuso de lógica existente (nada duplicado)

- `ProductionOrderService::create()` — sin cambios de comportamiento para
  ningún caller existente; `ProductionOrderDTO` solo ganó 2 campos
  opcionales (`idempotency_key`, `origin`) con default `null`/`'manual'`.
- `PaymentService::create()` — el anticipo pasa por el mismo chequeo de
  "no pagar más del saldo" que ya usa el resto del ERP. La implementación
  anterior de este mismo endpoint (deshabilitada en el hotfix del
  2026-09-24) usaba `Payment::create()` directo, sin ese chequeo — ya no.

## Token del bot — abilities explícitas y mínimas

Emitido con `php artisan whatsapp:provision-token` (opción `--force` para
scripting), **no** por `/api/v1/auth/login` (ese endpoint siempre emite
`['*']` — hallazgo ya documentado, sin resolver para tokens humanos, fuera
de alcance de esta tarea). Abilities: `whatsapp:catalog:read`,
`whatsapp:orders:create` — nada más.

### Hallazgo encontrado durante esta tarea, ya resuelto

Ya existía un usuario `role = 'integration'` (`n8n@autocarterascali.com`,
`id=8`, `active=true` desde antes) con un token real (`n8n-integration`,
emitido 2026-09-19) cuyas abilities eran mucho más amplias que lo acordado
acá: `clients:read`, `production-orders:read`, `payments:read`,
`stages:read` — exactamente lo que el usuario pidió que el bot **no**
pudiera hacer. Se confirmó `last_used_at = NULL` (nunca se usó) antes de
tocarlo. El comando `whatsapp:provision-token` reusa ese mismo usuario,
revoca ese token viejo y emite uno nuevo solo con las 2 abilities de arriba.

## Decisión pendiente (Fase 2 de la auditoría de seguridad)

Esto resuelve el hallazgo de "abilities decorativas" **solo para el bot de
WhatsApp**. El endpoint humano `/api/v1/auth/login` sigue emitiendo `['*']`
sin abilities para Admin/Director/Worker — sigue abierto en
`docs/business-rules/14-auditoria-seguridad-estado.md`.
