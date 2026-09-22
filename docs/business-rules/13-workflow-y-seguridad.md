# Workflow y seguridad

## Proceso obligatorio antes de cambios importantes

**Auditoría → hallazgos → propuesta → aprobación → implementación → validación → revisión.**

No implementar antes de que el usuario apruebe explícitamente la propuesta. "Cambio importante" incluye (no exhaustivo): cualquier cosa que toque permisos/roles, dinero, estados de pedido, esquema de base de datos, o comportamiento ya usado en producción.

## No inventar reglas de negocio ante ambigüedad

Si algo no está documentado en `docs/business-rules/` y no se puede deducir del código con evidencia suficiente, **preguntar antes de implementar, nunca asumir**. Esto aplica con especial fuerza a temas sensibles:

- Estados de pedido y sus transiciones válidas.
- Roles y permisos.
- Pagos y distribución financiera.
- Cancelaciones.
- Devoluciones.
- Eliminación de cualquier registro.
- Fechas de producción/compromiso.
- Recaudos (contraentrega o cualquier otro).
- Integraciones externas (qué pueden y no pueden hacer).

## Reglas de seguridad de datos y comandos prohibidos sin autorización explícita

- **Nunca ejecutar contra producción** sin autorización explícita del usuario para esa acción específica: `migrate:fresh`, `db:wipe`, `db:seed`, `TRUNCATE`, `DROP DATABASE`, `docker compose down -v`, ni equivalentes.
- **Para verificación visual/pruebas, usar registros creados con Factory y borrarlos al terminar** — nunca mutar una fila real de la base de datos, ni siquiera temporalmente con la intención de restaurarla después. Si no existe un Factory para el modelo necesario, señalarlo antes de recurrir a mutar un registro real.
- Antes de cualquier operación que pueda descartar trabajo no comiteado (`git reset --hard`, `git clean`, `git checkout --`), verificar el estado real (`git status`) primero.

## Aprobación individual de comandos

**Nunca "aceptar todo" automático** para comandos/cambios — cada acción con impacto real (especialmente las que tocan BD, producción, o archivos fuera del alcance explícito de la tarea) se aprueba de forma individual, no en bloque por adelantado.

## Formato recomendado de auditoría (hallazgos)

Para cada hallazgo:
- **Problema** — qué se encontró.
- **Evidencia** — archivo/línea, query real, comportamiento verificado (no supuesto).
- **Riesgo** — qué puede salir mal si no se atiende.
- **Propuesta** — la corrección sugerida, si aplica.
- **Decisión pendiente** — qué necesita decidir el usuario antes de proceder.

## Hallazgo abierto — riesgo real: el consecutivo de pedidos no usa la secuencia de Postgres que existe para eso

`ProductionOrderService::create()` (`app/Modules/Production/Services/ProductionOrderService.php:38`) calcula el consecutivo en PHP —
```php
$consecutive = (ProductionOrder::withTrashed()->max('consecutive') ?? 0) + 1;
```
— y lo pasa explícito al `INSERT`. Existe una secuencia real de Postgres (`production_orders_consecutive_seq`, migración `2026_09_18_000014_use_sequence_for_production_orders_consecutive.php`) puesta como `DEFAULT` de la columna `consecutive`, pero como el código siempre especifica el valor explícitamente, Postgres nunca llega a usar ese `DEFAULT` — la secuencia es infraestructura sin usar desde el punto de vista de la aplicación. El código queda expuesto a la misma condición de carrera que la secuencia debía resolver: dos creaciones de pedido simultáneas podrían leer el mismo `MAX(consecutive)` antes de que cualquiera de las dos inserte, generando un consecutivo duplicado.

**Verificado — consulta directa de duplicados existentes:**
```sql
SELECT consecutive, COUNT(*) FROM production_orders GROUP BY consecutive HAVING COUNT(*) > 1;
```
**Base de datos de desarrollo: 0 filas — no hay duplicados hoy.** No fue posible consultar la base de datos de producción desde este entorno (sin acceso); si se necesita ese dato, debe verificarse directamente en producción.

**Estado:** dado que no hay duplicados confirmados en dev, se trata como **pendiente de arreglar con calma, no urgente** — la corrección consiste en reemplazar el cálculo en PHP por `DB::raw("nextval('production_orders_consecutive_seq')")` (o dejar que el `INSERT` no especifique `consecutive` en absoluto, para que el `DEFAULT` de la columna se aplique solo). Confirmar el resultado en producción antes de decidir la urgencia real.

## Formato recomendado antes de implementar

- **Archivos a cambiar/crear/eliminar** — lista explícita.
- **Cambios funcionales** — qué comportamiento cambia para el usuario final.
- **Cambios de BD** — migraciones nuevas, columnas, constraints.
- **Riesgos** — qué podría romperse.
- **Pruebas** — cómo se va a verificar (suite automatizada, HTTP real, verificación visual con navegador real).
