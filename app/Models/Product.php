<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'pieces',
        'avg_production_days',
        'base_price',
        'photo',
        'active',
    ];

    protected $casts = [
        'active'             => 'boolean',
        'base_price'         => 'decimal:2',
        'pieces'             => 'integer',
        'avg_production_days'=> 'integer',
    ];

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }
}