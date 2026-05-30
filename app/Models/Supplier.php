<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = ['name', 'phone', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(MaterialPurchase::class);
    }
}