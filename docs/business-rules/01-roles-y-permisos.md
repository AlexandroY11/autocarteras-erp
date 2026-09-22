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

## Qué queda sin definir

- No hay una matriz formal exhaustiva de "cada endpoint × cada rol" — la fuente de verdad es el middleware/gate real en `routes/web.php` y `routes/api.php`. Ante una ruta nueva, preguntar qué rol(es) debería alcanzar, no asumir por analogía.
- Las abilities exactas que un token de Integration puede tener no están enumeradas en un solo lugar central — hay que revisar cada grupo de rutas API para saber qué abilities existen hoy.
