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
| 5 | Inyección de fórmulas en Excel/reportes | `272cbca` | Pusheado |
| 6 | Inyección JS vía atributos Alpine.js (`@click`, `x-data`) | `e14441d` | Pusheado |

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

### Fase 6 — resumen
`{!! !!}` está limpio en todo `resources/views` (3 usos, todos `asset()`
estático). El riesgo real era otro: `{{ $valor }}` interpolado crudo dentro
de un **atributo HTML** que Alpine.js evalúa como JS (`@click="..."`,
`x-data="{...}"`) — a diferencia de un `<script>` normal, el navegador
**sí** decodifica las entidades de un atributo antes de que Alpine lo
evalúe, reintroduciendo la comilla que `{{ }}` había neutralizado.
Verificado con ejecución real en navegador (Playwright + Edge, no solo
lectura de código):
- **`@click="showAlert.delete('delete-form-{{ $id }}', '...{{ $nombre }}...')"`**
  en `products/index.blade.php`, `clients/index.blade.php`,
  `stages/index.blade.php`, `users/index.blade.php` — un producto con
  `name = "X', (document.title='PWNED'), 'Y"` ejecutó JS real al hacer clic
  en "Eliminar" (`document.title` cambió).
- **`x-data="{ selected: '{{ $selected }}' }"`** en
  `components/searchable-select.blade.php` (usado por el selector de
  `color`/`sticker_color` en `orders/edit.blade.php` y, vía `old()`, en
  `orders/form.blade.php`) — una orden con `color` malicioso ejecutó JS con
  solo **abrir** la página de edición, sin ningún clic.
- Contraste verificado: la misma técnica dentro de un `<script>` normal
  (`orders/calendar.blade.php`, nombre de etapa) **no** se ejecuta — el
  navegador no decodifica entidades ahí, confirmando que el riesgo es
  específico del contexto "atributo HTML + Alpine", no de cualquier uso de
  `{{ }}` cerca de JS.

Corregido reemplazando la interpolación cruda por
`{{ \Illuminate\Support\Js::from($valor) }}` en los 5 sitios (los 4 botones
"Eliminar" + el componente `searchable-select`) — es el helper estándar de
Laravel para pasar un valor PHP a un contexto JS de forma segura (usa
`json_encode` con flags `JSON_HEX_*`), a diferencia de `addslashes()`
(usado en `calendar.blade.php`), que solo funciona por casualidad en
contexto `<script>` y no es suficiente en un atributo. Verificado en vivo
que la ejecución ya no ocurre (mismos payloads, mismos casos) y que un
valor normal (incluido uno con apóstrofe y `&`, ej. `O'Brien & Co`) se ve
exactamente igual que antes en el modal/selector.

Los 5 sitios afectados son Admin-escribe/Admin-ve hoy — mismo perfil de
riesgo acotado que la Fase 5. Matiz para cuando se reactive la integración
de WhatsApp/n8n: la validación comentada de `WhatsappOrderController`
restringe `color` a una lista cerrada (`in:Negro,Gris,Beige`) — ese vector
específico no se abriría con la integración tal como está redactada hoy.
`client_address`/`client_first_name`/`client_last_name` en esa misma
validación **sí son texto libre** — el vector de `clients/index.blade.php`
sí se volvería explotable por un tercero externo el día que se reactive.

## Confirmado externamente por el usuario (2026-09-23) — 1c y 2c cerrados

Ambos puntos quedaron pendientes de Fase 2 porque requerían acceso a
producción, que Claude Code no tiene. El usuario los validó directamente
contra producción, con evidencia real, no solo lectura de configuración:

- **1c — throttle de login vs. IP falsificada: sin riesgo real, cerrado.**
  El usuario hizo 8 intentos de login por API conectando directo a la IP
  del servidor de producción (saltándose Cloudflare) y rotando el header
  `X-Forwarded-For` en cada intento (`1.1.1.1`, `2.2.2.2`, etc.). El bloqueo
  `429` se activó igual en el intento 6 y se mantuvo en el 7 y 8, pese a que
  cada request llevaba una IP falsa distinta. Conclusión: **Traefik
  sobrescribe el header con la IP real de la conexión TCP** antes de que
  llegue a Laravel — `trustProxies(at: '*')` es permisivo a nivel de código,
  pero la capa de proxy real en producción neutraliza la suplantación. No
  hace falta tocar código.
- **2c — `SESSION_SECURE_COOKIE`: confirmado, correcto.** El usuario
  verificó en la respuesta real de producción que las cookies de sesión y
  CSRF llegan con el flag `Secure` puesto.

Nota aparte, no relacionada con código ni con esta auditoría: al hacer esta
validación el usuario descubrió que **EasyPanel no tenía auto-deploy
activo** — las Fases 1-6 llevaban horas en `origin/master` sin llegar a
producción hasta un deploy manual. Es una decisión de infraestructura que
el usuario va a tomar por su cuenta (activar auto-deploy o dejarlo manual a
propósito) — no es algo que Claude Code deba tocar ni asumir.

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

Ninguna todavía identificada — Fases 1-6 completas. Próxima fase a definir
cuando el usuario lo pida.

## Cómo continuar en una sesión nueva

1. Leer este archivo primero para saber en qué quedó la auditoría.
2. Antes de tocar código nuevo de seguridad, confirmar si el commit de la
   fase más reciente ya llegó a producción (el deploy es manual — un push a
   `origin/master` no basta, ver nota de EasyPanel arriba).
3. Seguir el mismo formato por fase: solo auditoría primero (Hallazgo →
   Evidencia → Riesgo → Propuesta, sin tocar código), esperar aprobación
   explícita por punto, implementar, verificar en vivo con datos Factory
   desechables (nunca mutar filas reales), mostrar diff, comitear solo
   cuando se apruebe, nunca hacer push sin instrucción explícita.
