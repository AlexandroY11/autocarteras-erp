<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaravelWebauthn\Models\WebauthnKey as BaseWebauthnKey;

class WebauthnKey extends BaseWebauthnKey
{
    use HasFactory;

    // Heredamos todo de la librería
}
