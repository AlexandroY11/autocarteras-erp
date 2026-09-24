# Índice de reglas de negocio — AutoCarteras Cali

Cada archivo documenta un dominio con esta estructura por regla: **qué es** (hecho establecido, con evidencia de código cuando aplica) y, si corresponde, **qué queda explícitamente sin definir** — para saber qué preguntar antes de asumir.

| Archivo | Qué cubre |
|---|---|
| [01-roles-y-permisos.md](01-roles-y-permisos.md) | Los 4 roles (Admin, Director, Worker, Integration) y qué puede ver/hacer cada uno. |
| [02-produccion-y-etapas.md](02-produccion-y-etapas.md) | Las etapas de producción reales (activas e inactivas), quién puede avanzar cuáles. |
| [03-estados-de-pedido.md](03-estados-de-pedido.md) | El ciclo de vida de un pedido: `status` y `dispatch_status`, qué es terminal y qué no. |
| [04-despachos-y-envio.md](04-despachos-y-envio.md) | Cómo funciona el despacho manual, la guía, y el bloqueo por saldo de envío pendiente. |
| [05-pagos-y-financiero.md](05-pagos-y-financiero.md) | Snapshot de precios, distribución de pagos (envío primero), recaudo contraentrega. |
| [06-eliminacion-y-cancelacion.md](06-eliminacion-y-cancelacion.md) | Qué se puede cancelar/eliminar y qué no — sin política de reversión de pagos inventada. |
| [07-fechas-de-produccion.md](07-fechas-de-produccion.md) | Cómo se calcula la fecha compromiso (días hábiles, festivos). |
| [08-catalogo-y-colores.md](08-catalogo-y-colores.md) | Catálogo de colores y sugerencias de calcomanía — estado real de conexión (hallazgo pendiente). |
| [09-creacion-de-pedidos.md](09-creacion-de-pedidos.md) | Qué calcula el backend al crear un pedido y qué nunca debe aceptarse del cliente. |
| [10-contratos-api-y-dtos.md](10-contratos-api-y-dtos.md) | Contrato financiero actual vs. antiguo, DTOs de creación/actualización (con un riesgo real documentado). |
| [11-webauthn.md](11-webauthn.md) | Passkeys/WebAuthn: qué se conserva, cómo se resuelve el usuario, protección contra fuerza bruta. |
| [12-arquitectura.md](12-arquitectura.md) | Stack, qué no introducir sin necesidad, regla de centralización Web/API. |
| [13-workflow-y-seguridad.md](13-workflow-y-seguridad.md) | El proceso obligatorio antes de cambios importantes, y las reglas de seguridad de datos. |
| [14-auditoria-seguridad-estado.md](14-auditoria-seguridad-estado.md) | Estado vivo de la auditoría de seguridad por fases: qué está comiteado, qué falta confirmar externamente, qué sigue pendiente. |

**Hallazgos abiertos** (código actual contradice o no coincide con lo documentado — ver el archivo correspondiente para el detalle):
- `08-catalogo-y-colores.md` — `cart_colors`/`sticker_color_suggestions` son scaffolding sin conectar; el color sigue hardcodeado en 2 lugares.
- `10-contratos-api-y-dtos.md` — no existe un DTO de actualización separado; `price`/`client_id`/`product_id`/`due_date` son editables en un pedido con pagos ya registrados. Marcado como **riesgo real, pendiente de decisión de negocio**.
- `10-contratos-api-y-dtos.md` — `PaymentService::summary()` mezcla el contrato financiero antiguo y el actual en la misma respuesta. Marcado como **transicional**.
- `04-despachos-y-envio.md` — `shipping_price = NULL` se trata como `0` (gratis) en el código actual, no como "desconocido". Pendiente de que el usuario confirme la cifra real en producción antes de decidir si se corrige.
- `09-creacion-de-pedidos.md` — `price` y `due_date` se aceptan directamente del formulario de creación (exclusivo Admin) sin recalcularse server-side. Pendiente de decisión de negocio: ¿función real deseada, o debe bloquearse?
- `13-workflow-y-seguridad.md` — el consecutivo de pedidos se calcula en PHP (`MAX+1`) en vez de usar la secuencia de Postgres que ya existe para eso — riesgo real de duplicados bajo concurrencia. Verificado: 0 duplicados en dev hoy; no verificado en producción. Tratado como no urgente mientras no haya duplicados confirmados.
- `03-estados-de-pedido.md` — `ProductionOrder::getTimeStatusAttribute()` (`app/Models/ProductionOrder.php:312-330`, guarda en línea 314) no contempla `status = 'cancelled'`, solo `'done'` — un pedido cancelado puede seguir mostrando un badge de urgencia ("CRÍTICO"/"VENCIDO") junto al badge "Cancelado" en `orders/index.blade.php`. `getTimeTrackingLabelAttribute()` sí excluye ambos estados; la inconsistencia es solo en el accessor que usa esta vista. Pendiente de decisión de negocio, no corregido.
- `01-roles-y-permisos.md` — las abilities de Sanctum son decorativas: `AuthService::login()` (`app/Modules/Auth/Services/AuthService.php:24`) emite todo token con ability `['*']` (nunca abilities restringidas), así que los middlewares `ability:*:read` en `routes/api.php` no bloquean a nadie autenticado. Bajo riesgo mientras la integración WhatsApp/n8n esté pausada; debe resolverse antes de conectar cualquier consumidor externo. No implementado a propósito (Auditoría Fase 2, 2026-09-23).
