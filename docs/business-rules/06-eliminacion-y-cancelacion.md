# Eliminación y cancelación

## Los pedidos no se eliminan físicamente — se cancelan

No existe ninguna ruta de borrado (`DELETE`/`destroy`) para `production_orders` en `routes/web.php` ni `routes/api.php` (verificado). La única forma de retirar un pedido es cancelarlo (`status = cancelled`, ver `03-estados-de-pedido.md`), lo que conserva el registro completo, su historial de etapas (`order_stages`) y sus pagos.

## Los pagos no tienen política de reversión/corrección definida

No existe ninguna ruta de borrado para `payments` (verificado — no hay `destroy` en `PaymentController` web ni API, ni ruta `DELETE` para pagos en ningún lado). **Esto es intencional, no un descuido**: ante un pago mal registrado, la política correcta es **preferir bloquear/no construir un endpoint de eliminación**, en vez de inventar un sistema de reversos, notas crédito o compensaciones que no ha sido definido por el negocio.

**No inventar** ninguno de estos mecanismos sin aprobación explícita:
- Reversión/anulación de un pago ya registrado.
- Notas crédito.
- Compensaciones entre pedidos.

## Los registros históricos `type = 'advance'` se conservan tal cual

`production_orders.advance_payment` fue una columna que existió y se eliminó (migración `2026_09_18_000007_drop_advance_payment_from_production_orders_table.php`), después de que una migración previa (`2026_09_18_000006_backfill_advance_payment_into_payments.php`) copió cada valor no-nulo/no-cero a una fila real en `payments` con `type = 'advance'`. **`payments` es la única fuente de verdad para pagos hoy** — `advance_payment` ya no existe como columna, no debe reintroducirse ni referenciarse como si existiera.

## Qué queda sin definir

- No hay una tabla de auditoría (`activity_log`/`audit_log`) en el proyecto — no hay forma de reconstruir "quién cambió qué y cuándo" más allá de `updated_at`/`created_at` de cada tabla y los campos `registered_by`/`created_by`/`assigned_to` que ya existen puntualmente. Si se necesita trazabilidad más fina, es una decisión de negocio nueva, no algo que ya exista.
- No hay regla sobre eliminación de clientes (`Client`) ni productos (`Product`) — no asumir que se comportan igual que pedidos/pagos sin verificar.
