<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\WebauthnKey>
 */
class WebauthnKeyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->word() . ' key',
            'credentialId' => random_bytes(32),
            'type' => 'public-key',
            'transports' => [],
            'attestationType' => 'none',
            'trustPath' => ['type' => 'Webauthn\TrustPath\EmptyTrustPath'],
            'aaguid' => '00000000-0000-0000-0000-000000000000',
            'credentialPublicKey' => random_bytes(64),
            'counter' => 0,
        ];
    }
}
