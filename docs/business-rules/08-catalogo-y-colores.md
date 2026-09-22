# Catálogo y colores

## Intención de negocio

- **`cart_colors`** debería ser el catálogo cerrado de colores de cartera (no un texto libre) — para no duplicar la lista de opciones válidas en cada formulario/frontend que necesite mostrarla.
- El **color de la calcomanía** es texto libre, con **sugerencias no restrictivas** (`sticker_color_suggestions`) — el usuario puede escribir cualquier cosa, la tabla es solo para ayudar a autocompletar, no para validar contra una lista cerrada.
- Ninguno de los dos (color de cartera ni color de calcomanía) afecta precio ni tiempo de producción.

## Hallazgo — `cart_colors` y `sticker_color_suggestions` son scaffolding, no están conectadas todavía

Verificado: ningún `Model` ni `Controller` en `app/` referencia `cart_colors` ni `sticker_color_suggestions` (ambas tablas existen en la BD desde las migraciones `2026_09_18_000010`/`000011`, con datos semilla, pero sin ningún consumidor). **El color de cartera sigue hardcodeado, duplicado en 2 lugares:**

- `resources/views/orders/form.blade.php:320-325` — array fijo en el propio Blade: `Negro`, `Gris`, `Beige`.
- `app/Http/Controllers/Api/WhatsappOrderController.php` — validación `'color' => 'required|in:Negro,Gris,Beige'`.

**El día que se conecte `cart_colors` de verdad, hay que migrar estos 2 lugares** para que lean de la tabla en vez de la lista fija — y confirmar que los 2 se mantienen sincronizados hasta entonces (cualquier color nuevo hoy requiere tocar ambos archivos a mano).

## Qué queda sin definir

- No hay un `CartColor` Model ni un endpoint que exponga el catálogo — si se decide conectar `cart_colors`, hay que diseñar esa pieza desde cero, no asumir que ya existe algo parcial más allá del schema.
- No hay regla sobre si `sticker_color_suggestions` se usa como autocomplete real en algún formulario hoy — verificado que no, pero no se sabe si es la intención inmediata conectarlo o si sigue siendo trabajo futuro sin fecha.
