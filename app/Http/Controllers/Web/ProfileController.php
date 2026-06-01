<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use LaravelWebauthn\Models\WebauthnKey;

class ProfileController extends Controller
{
    public function index()
    {
        $keys = WebauthnKey::where(
            'user_id',
            auth()->id()
        )
        ->latest()
        ->get();

        return view('profile', [
            'keys' => $keys,
        ]);
    }
}