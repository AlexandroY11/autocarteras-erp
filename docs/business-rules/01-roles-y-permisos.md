# Roles y permisos

## Los 4 roles (`users.role`)

- **Admin** — acceso total, incluyendo todo lo financiero (precios, saldos, reportes financieros, cartera total). Único rol que puede despachar órdenes, gestionar catálogo (productos, clientes, etapas, usuarios), y descargar cualquier reporte Excel/PDF.
- **Director** — operativo: ve pedidos, producción, calendario, filtrado por su propia habilidad de etapa (mismo criterio que Worker). **Sin acceso a nada financiero sensible ni a reportes financieros** — no ve precio/saldo en las cards de pedidos, no puede acceder a `/reports/*`.
- **Worker** — solo sus tareas/etapas asignadas (por `user_skills`), sin acceso financiero, sin acceso a administración (usuarios, catálogo, reportes).
- **Integration** — cuenta técnica para consumidores API (ej. n8n/WhatsApp). Usa **abilities de Sanctum explícitas y mínimas** por token, nunca hereda permisos de un rol humano. No aparece en el panel humano de gestión de usuarios (excluida a propósito de `User::ROLE_LABELS`).

## Cómo se aplica en código

- `User::isAdmin()`, `isDirector()`, `isWorker()`, `isOperative()` (= Worker o Director) son los helpers centralizados — nunca comparar `role === '...'` directamente en una vista o controlador nuevo.
- Middleware `admin` (`EnsureUserIsAdmin`) protege todas las rutas exclusivas de Admin en `routes/web.php` (catálogo, reportes, despacho, pagos).
- Worker/Director comparten una sola vista ("Mis tareas"), filtrada por habilidad — no hay una vista separada por rol para esto.
- Los 3+ reportes Excel/PDF (`/reports/*`) están protegidos con **doble gate**: middleware `admin` en la ruta + `abort_unless(auth()->user()->isAdmin(), 403)` explícito dentro de cada método del controlador. Todo reporte nuevo debe seguir este mismo patrón, sin excepción.
- Integration: en `routes/api.php`, cada ruta exige una ability específica (`ability:products:read`, `ability:clients:read`, `payments:read`, etc.) vía middleware de Sanctum — nunca `auth:sanctum` a secas para una ruta que un token de integración pueda alcanzar.

## Hallazgo — las abilities de Sanctum son decorativas hoy: todo token tiene acceso total

La intención documentada arriba (Integration usa "abilities explícitas y mínimas por token") **no está implementada**. `app/Modules/Auth/Services/AuthService.php:24` emite todos los tokens con `$user->createToken('auth_token')->plainTextToken` — sin array de abilities, lo que por default de Sanctum otorga la ability comodín `['*']` (todas). Es el único lugar del código donde se crean tokens (verificado por grep en todo `app/`). Los middlewares `ability:products:read`, `ability:clients:read`, `ability:stages:read`, `ability:payments:read`, `ability:production-orders:read` que sí existen en `routes/api.php` y los módulos, por lo tanto, no bloquean a nadie que ya haya pasado el login — cualquier token válido las satisface todas.

**Riesgo hoy: bajo** — quien puede loguearse hoy es solo staff interno, ya separado por rol vía middleware `admin` para escritura (ese control sí funciona, es independiente de abilities). **Se vuelve un riesgo real el día que se conecte una integración externa** (el bot de WhatsApp/n8n de `WhatsappOrderController.php`, hoy pausado): si obtiene un token vía este mismo endpoint de login, tendría acceso de lectura completo a clientes/productos/pagos/órdenes, no solo a lo que necesita.

**Pendiente, no implementado a propósito** — decisión del usuario del 2026-09-23: no es urgente mientras la integración esté pausada, pero debe resolverse antes de conectar cualquier consumidor externo. Dos caminos posibles: (a) pasar un array de abilities real a `createToken()` según el caso de uso, o (b) si el control real seguirá siendo por rol/gate y no por ability, retirar los middlewares `ability:*` para no sugerir una protección que no existe.

## Qué queda sin definir

- No hay una matriz formal exhaustiva de "cada endpoint × cada rol" — la fuente de verdad es el middleware/gate real en `routes/web.php` y `routes/api.php`. Ante una ruta nueva, preguntar qué rol(es) debería alcanzar, no asumir por analogía.
- Las abilities exactas que un token de Integration puede tener no están enumeradas en un solo lugar central — hay que revisar cada grupo de rutas API para saber qué abilities existen hoy.
- **Decisión pendiente (Auditoría Fase 3, 2026-09-23):** `ProductionOrderController::show()` (web, `/production-orders/{id}`) y su equivalente API no filtran por habilidad/etapa del usuario — un Worker/Director puede ver el detalle operativo de **cualquier** orden, no solo las de su etapa asignada, a diferencia de "Mis tareas" (que sí filtra por `user_skills`). Ya no incluye datos financieros (corregido en la misma auditoría), pero sigue exponiendo cliente, producto, trazabilidad completa y estado de despacho de órdenes fuera del alcance del usuario. Lectura inicial del usuario: probablemente debería tener el mismo filtro que "Mis tareas" — pendiente de decidir con calma, no implementado todavía.
