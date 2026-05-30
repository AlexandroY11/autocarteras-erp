<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $fillable = ['name', 'unit', 'supplier_id', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(MaterialPurchase::class);
    }

    public function getUnitLabelAttribute(): string
    {
        return match($this->unit) {
            'kg'     => 'Kilogramos',
            'g'      => 'Gramos',
            'lt'     => 'Litros',
            'ml'     => 'Mililitros',
            'unidad' => 'Unidades',
            default  => $this->unit,
        };
    }
}