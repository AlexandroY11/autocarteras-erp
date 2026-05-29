<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}