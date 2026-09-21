<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Cartera ' . fake()->unique()->word(),
            'description' => fake()->sentence(),
            'pieces' => fake()->numberBetween(1, 5),
            'avg_production_days' => fake()->numberBetween(3, 15),
            'base_price' => fake()->numberBetween(100, 900) * 1000,
            'shipping_price' => fake()->numberBetween(10, 30) * 1000,
            'photo' => null,
            'active' => true,
        ];
    }
}
