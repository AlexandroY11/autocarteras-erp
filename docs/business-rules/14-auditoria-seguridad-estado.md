# Estado de la auditoría de seguridad por fases

Registro de continuidad entre sesiones — para no perder el hilo de qué se
auditó, qué se corrigió, qué sigue pendiente de confirmación externa y qué
decisiones de negocio quedaron abiertas sin implementar. Última actualización:
**2026-09-23**.

## Fases completadas

| Fase | Tema | Commit | Estado |
|---|---|---|---|
| 1 | Gestión de credenciales y secretos | `05df65c` | Pusheado |
| 2 | Autenticación y control de acceso | `390ce4c` | Pusheado |
| 3 | Integridad financiera y autorización a nivel de objeto (IDOR) | `0a07f49` | Pusheado |
| 4 | Despachos/producción — centralización de guards de estado | `d44e012` | Pusheado |
| 5 | Inyección de fórmulas en Excel/reportes | `272cbca` | Comiteado, sin push |

El usuario revisa cada commit con `git show` completo antes de aprobar el
push — **no hacer `git push` sin instrucción explícita**, aunque hayan
pasado varias sesiones.

### Fase 1 — resumen
`docker-compose.yml` con `POSTGRES_PASSWORD` hardcodeado → ahora lee de
`${DB_PASSWORD}`. `DatabaseSeeder.php` con contraseñas `admin123`/`worker123`
→ contraseñas aleatorias (`Str::password(16)`) impresas una sola vez en
consola. `.dockerignore` no excluía `.env` (riesgo de hornear secretos reales
en la imagen vía el `COPY . .` del Dockerfile) → corregido. Eliminado
`docker/php/Dockerfile` huérfano sin ninguna referencia en el repo.

### Fase 2 — resumen
`/login` (web) y `/api/v1/auth/login` no tenían ningún rate limit → throttle
de 5/min por email+IP (mismo criterio que ya usaba WebAuthn). `logout()` no
invalidaba la sesión ni rotaba el CSRF token → corregido. `Auth::attempt()`
forzaba "remember me" en todo login → ahora es opcional vía checkbox.
Desactivar un usuario no revocaba nada (ni sesión ni token Sanctum, que no
expira por defecto) → se agregó revocación explícita de tokens en
`UserController::update()` + middleware `EnsureUserIsActive` aplicado
globalmente (web y api) que revalida `active` en cada request. De paso se
encontró y corrigió un bug real independiente: el checkbox "Usuario Activo"
en `users/form.blade.php` nunca funcionó (faltaba el `<input type="hidden">`
de respaldo) — nadie había podido desactivar a nadie desde la UI hasta ese
commit.

### Fase 3 — resumen
`orders/show.blade.php` mostraba precio/envío/saldos/pagos de **cualquier**
orden a Director/Worker sin ningún gate — contradice directamente
`01-roles-y-permisos.md` ("sin acceso financiero"). Corregido con
`@if(auth()->user()->isAdmin())`. En la API, `ProductionOrderController` y
`PaymentController` devolvían esos mismos campos a cualquier token
autenticado (las abilities de Sanctum son decorativas, ver hallazgo de Fase
2) — se agregaron `ProductionOrderResource` y `PaymentResource` que ocultan
`price`/`shipping_price`/`payments`/saldos cuando el usuario no es Admin, sin
tocar el resto de los campos. Se incluyó `advanceStage()` en el mismo fix por
ser la misma causa raíz en el mismo archivo.

### Fase 4 — resumen
`ProductionOrderService::cancel()` y `advanceStage()` no validaban nada por sí
solos — los guards reales (bloquear cancelación si ya se despachó, bloquear
avance si `status` es done/cancelled o si la orden está en la etapa "Enviado")
vivían duplicados, idénticos, en el controller web y en el de API. Ningún
bypass activo (ambas copias eran correctas), pero riesgo de mantenimiento. Se
movieron los 3 guards al servicio; los controllers quedaron con un solo
`try/catch` cada uno.

### Fase 5 — resumen
Los 4 `Export` (`ClientsReportExport`, `ProductsReportExport`,
`OperationalFollowupReportExport`, `FinancialFullReportExport`) escriben
`client.full_name`/`address` y `product.name`/`description` a celdas de Excel
sin sanear. Verificado con una prueba real (generar el `.xlsx` y reabrirlo con
`PhpSpreadsheet\IOFactory`): un valor que empieza con `=` y parsea como
fórmula válida se guarda como celda tipo fórmula ejecutable de verdad
(`Cell::getDataType() === 'f'`) — `+`/`-`/`@` NO producen ese resultado en
este stack (esa checklist es de CSV, no de `.xlsx` genuino con metadata de
tipo de celda; confirmado, no asumido). Corregido anteponiendo un apóstrofe a
cualquier valor que empiece con `=` antes de escribirlo (trait
`App\Exports\Concerns\EscapesFormulaInjection`), sin tocar cómo se guarda el
dato en BD ni la validación de esos campos.

## Pendiente de confirmación externa (el usuario lo está verificando, no yo)

- **1c** — `trustProxies(at: '*')` en `bootstrap/app.php` confía en cualquier
  proxy para resolver `$request->ip()`. El throttle por IP (login y WebAuthn)
  depende de que el proxy real de producción sobrescriba `X-Forwarded-For`
  del cliente en vez de anexarlo. El usuario va a confirmar esto con quien
  administra el proxy de producción.
- **2c** — Falta confirmar si `SESSION_SECURE_COOKIE=true` está explícito en
  el `.env` real de producción (el código no lo fuerza, depende del
  auto-detect de Laravel según si la request es HTTPS). El usuario lo va a
  revisar directamente en el servidor.

No asumir un resultado para ninguno de los dos — preguntar si ya se
confirmaron antes de dar por cerrada la Fase 2.

## Decisiones de negocio abiertas, documentadas pero sin implementar

- **`docs/business-rules/01-roles-y-permisos.md`** — las abilities de Sanctum
  son decorativas hoy (todo token se emite con `['*']`). No es urgente
  mientras la integración WhatsApp/n8n esté pausada, pero debe resolverse
  antes de conectar cualquier consumidor externo.
- **`docs/business-rules/01-roles-y-permisos.md`** — `ProductionOrderController::show()`
  (web y API) no filtra por habilidad/etapa del usuario — un Worker/Director
  puede ver el detalle operativo de cualquier orden, no solo las suyas (ya no
  ve lo financiero, eso sí se corrigió). El usuario cree que debería
  filtrarse igual que "Mis tareas", pero lo va a decidir con calma. **No
  implementar sin que lo confirme explícitamente.**
- **Fase 5 (Excel/reportes)** — alternativa **no implementada a propósito**:
  en vez de (o además de) escapar al exportar, se podría **rechazar al
  guardar** — validar en `ClientController`/`ProductController` (y en la
  creación de cliente inline dentro de `ProductionOrderController::store()`)
  que `first_name`/`last_name`/`address`/`name`/`description` no empiecen con
  `=`. Esto cambiaría la validación de campos existentes, así que es una
  decisión de negocio que el usuario prefiere pensar con calma — el fix ya
  implementado (escapar al exportar) no depende de esto y no cambia lo que un
  Admin puede escribir.

## Fase pendiente

- **Fase 6 — XSS en campos de texto libre**: revisar si `observations`
  (orden), `notes` (pago) u otros campos de texto libre se renderizan en
  algún lugar con `{!! !!}` en vez de `{{ }}` (stored XSS). Candidato
  propuesto durante la Fase 5, todavía sin auditar.

## Cómo continuar en una sesión nueva

1. Leer este archivo primero para saber en qué quedó la auditoría.
2. Antes de tocar código nuevo de seguridad, preguntar si 1c/2c ya se
   confirmaron y si el commit de la fase más reciente ya se hizo push.
3. Seguir el mismo formato por fase: solo auditoría primero (Hallazgo →
   Evidencia → Riesgo → Propuesta, sin tocar código), esperar aprobación
   explícita por punto, implementar, verificar en vivo con datos Factory
   desechables (nunca mutar filas reales), mostrar diff, comitear solo
   cuando se apruebe, nunca hacer push sin instrucción explícita.
