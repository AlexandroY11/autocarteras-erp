<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'order',
        'color',
        'active',
        'auto_complete',
    ];

    protected $casts = [
        'active'        => 'boolean',
        'auto_complete' => 'boolean',
    ];

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class, 'current_stage_id');
    }

    /**
     * ID real de la etapa "Enviado" — está desactivada (active=false) pero
     * sigue existiendo por datos históricos. Buscar por nombre en vez de un
     * ID quemado a mano: el ID numérico depende de la secuencia de cada BD
     * (dev/test/producción) y no es estable entre entornos.
     */
    public static function enviadoId(): ?int
    {
        return static::where('name', 'Enviado')->value('id');
    }
}