# Fechas de producción

## Depende de `products.avg_production_days`

Cada producto tiene su propio promedio de días de producción (`products.avg_production_days`), usado como base para calcular la fecha compromiso de un pedido nuevo.

## El backend es la fuente de verdad

`BusinessDaysService::calculateDueDate()` calcula la fecha compromiso en el servidor al crear el pedido — el frontend solo **muestra una estimación** (para que el usuario vea algo antes de enviar el formulario), nunca decide ni envía la fecha final: el backend siempre recalcula y es lo que efectivamente se guarda. Ver `09-creacion-de-pedidos.md` sobre por qué la fecha nunca debe aceptarse tal cual del cliente.

## Días hábiles: excluyen domingos y festivos de la tabla `holidays`

`BusinessDaysService` (`calculateDueDate()` y `businessDaysUntil()`) cuenta como día hábil cualquier día que **no sea domingo** y **no esté en la tabla `holidays`** para el año correspondiente (carga festivos del año actual y, si se cruza a noviembre/diciembre, también del siguiente). **Los sábados sí cuentan como día hábil** — no se excluyen.

## Qué queda sin definir

- No hay una fuente automática que mantenga actualizada la tabla `holidays` año a año — hay que confirmar cómo se carga/mantiene antes de asumir que un año nuevo ya tiene sus festivos cargados.
- No hay regla sobre qué pasa si `avg_production_days` de un producto cambia después de que ya existen pedidos activos con ese producto — la fecha compromiso ya calculada de esos pedidos no se recalcula retroactivamente (coherente con el principio de snapshot de `05-pagos-y-financiero.md`, pero no está escrito como regla explícita en ningún lado del código).
