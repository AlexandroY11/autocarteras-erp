<?php

namespace Database\Factories;

use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'production_order_id' => ProductionOrder::factory(),
            'amount' => fake()->numberBetween(10, 200) * 1000,
            'type' => fake()->randomElement(['advance', 'partial', 'final']),
            'payment_method' => fake()->randomElement(['efectivo', 'nequi']),
            'notes' => null,
            'paid_at' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'registered_by' => User::factory(),
        ];
    }
}
