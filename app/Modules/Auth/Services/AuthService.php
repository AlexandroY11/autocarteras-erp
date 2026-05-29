<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\DTOs\LoginDTO;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(LoginDTO $dto): array
    {
        if (!Auth::attempt(['email' => $dto->email, 'password' => $dto->password])) {
            throw new \Exception('Credenciales incorrectas.', 401);
        }

        $user = Auth::user();

        if (!$user->active) {
            Auth::logout();
            throw new \Exception('Usuario inactivo. Contacta al administrador.', 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function me(User $user): User
    {
        return $user;
    }
}