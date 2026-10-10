<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Teléfono desde el que un vendedor le escribe al bot de WhatsApp. Varios
 * por vendedor; uno solo puede apuntar a un vendedor (unique en BD).
 */
class SellerPhone extends Model
{
    protected $fillable = [
        'phone',
        'user_id',
    ];

    /**
     * Única fuente de la normalización de teléfonos de vendedor — la usan
     * tanto el comando que los vincula como la resolución del bot, para que
     * "+573172196131", "573172196131" y "+57 317 219 6131" sean el mismo
     * registro.
     *
     * Deliberadamente no agrega ni adivina el indicativo del país: si el bot
     * manda un número sin él, no resuelve y el endpoint responde 422. Falla
     * cerrado antes que asignarle la venta al vendedor equivocado.
     */
    public static function normalize(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
