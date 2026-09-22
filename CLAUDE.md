# AutoCarteras Cali — guía rápida para Claude Code

**Regla más importante: no inventar reglas de negocio.** Si algo no está documentado en `docs/business-rules/` o no se puede deducir del código con evidencia suficiente (archivo/línea, constraint real de BD, comportamiento verificado), **preguntar antes de implementar — nunca asumir.**

## Qué es este proyecto

ERP/CRM para **AutoCarteras Cali**, un taller que fabrica y despacha carteras (forros) a medida para vehículos. Stack: **Laravel 12**, **PHP 8.4**, **PostgreSQL**, **Docker** (nginx + php-fpm vía supervisord, worker de cola condicional solo en producción), **Sanctum** para autenticación de integraciones API, Eloquent + Blade/Alpine.js para el panel web.

## Orden de prioridad al tomar decisiones

1. **Preservar datos** — nunca perder información real, ni siquiera temporalmente (ver reglas de seguridad de datos abajo).
2. **Preservar reglas de negocio** — el comportamiento documentado (o verificado en código) es la fuente de verdad, no lo que "parecería más limpio".
3. **Centralizar lógica** — una sola fuente de verdad por regla (Service/Model), nunca duplicada entre Web y API.
4. **Seguridad** — roles, gates, protección de rutas.
5. **Trazabilidad** — que quede evidencia de qué pasó y por qué (aunque hoy no exista un log de auditoría formal — ver `docs/business-rules/06-eliminacion-y-cancelacion.md`).
6. **Simplicidad** — la solución más simple que cumpla lo anterior.
7. **Refactorización** — solo cuando no compromete nada de lo anterior, y nunca como excusa para tocar código sin necesidad concreta.

## Reglas de seguridad de datos (siempre, sin excepción)

- **Nunca ejecutar contra producción sin autorización explícita del usuario**: `migrate:fresh`, `db:wipe`, `db:seed`, `TRUNCATE`, `DROP DATABASE`, `docker compose down -v`, ni ningún comando equivalente que destruya datos.
- **Para verificación visual o pruebas, usar registros creados con Factory y borrarlos al terminar.** Nunca mutar filas reales de la base de datos, ni siquiera temporalmente con la intención de restaurarlas después — un registro real nunca se toca si existe una alternativa sintética.
- Antes de cualquier operación que pueda descartar trabajo (`git reset --hard`, `git clean`, etc.), verificar el estado real primero.

## Índice de reglas de negocio

Ver `docs/business-rules/00-index.md` para la lista completa con una frase por archivo. Léelos según la tarea — no hace falta leer los 13 para cada cambio.
