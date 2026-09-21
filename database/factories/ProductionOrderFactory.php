<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Product;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ProductionOrder>
 */
class ProductionOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'consecutive' => fake()->unique()->numberBetween(1, 999999),
            'client_id' => Client::factory(),
            'product_id' => Product::factory(),
            'color' => fake()->safeColorName(),
            'sticker' => false,
            'sticker_color' => null,
            'observations' => null,
            'price' => fake()->numberBetween(100, 900) * 1000,
            'shipping_price' => fake()->numberBetween(10, 30) * 1000,
            'due_date' => fake()->dateTimeBetween('+3 days', '+30 days')->format('Y-m-d'),
            // Ojo: Laravel evalua toda esta definicion (incluida esta
            // Stage::factory()) ANTES de aplicar cualquier override que se
            // pase a create([...]) — sobrescribir 'current_stage_id' igual
            // deja una etapa activa "fantasma" con order aleatorio (1-10)
            // en la BD. Si un test necesita etapas con order especifico y
            // determinista (ej. para probar orderBy('order')), usa valores
            // fuera de ese rango (ej. 1000+) para no chocar con la fantasma.
            'current_stage_id' => Stage::factory(),
            'status' => 'pending',
            'dispatch_status' => null,
            'created_by' => User::factory(),
        ];
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
            'current_stage_id' => null,
            'dispatch_status' => 'pending_dispatch',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'dispatch_status' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day')->format('Y-m-d'),
        ]);
    }
}
