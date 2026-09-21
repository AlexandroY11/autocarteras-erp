<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Stage>
 */
class StageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'order' => fake()->numberBetween(1, 10),
            'color' => fake()->hexColor(),
            'active' => true,
            'auto_complete' => false,
        ];
    }

    public function autoComplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_complete' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
