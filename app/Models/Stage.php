<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'order',
        'color',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class, 'current_stage_id');
    }
}