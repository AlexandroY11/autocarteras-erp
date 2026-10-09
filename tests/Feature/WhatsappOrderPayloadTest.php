<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Client;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Campos opcionales nuevos del contrato del bot: observations (texto que
 * dicta el vendedor) y payment_method del anticipo. observations comparte
 * una sola columna con la nota automática de conflicto de dirección que
 * escribe resolveClient(), así que la prueba central es que convivan.
 */
class WhatsappOrderPayloadTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private City $city;

    protected function setUp(): void
    {
        parent::setUp();

        Stage::factory()->create(['order' => 1]);

        $this->product = Product::factory()->create([
            'active' => true,
            'base_price' => 300000,
            'shipping_price' => 20000,
        ]);

        $department = Department::factory()->create();
        $this->city = City::factory()->create(['department_id' => $department->id]);

        Sanctum::actingAs(
            User::factory()->integration()->create(),
            ['whatsapp:orders:create'],
        );
    }

    private function botPayload(array $overrides = []): array
    {
        return array_merge([
            'idempotency_key' => 'wa-payload-base',
            'client_first_name' => 'Ana',
            'client_last_name' => 'Gómez',
            'client_phone' => '3001112233',
            'client_email' => 'ana@example.com',
            'city_id' => $this->city->id,
            'department_id' => $this->city->department_id,
            'address' => 'Calle 1 #2-3',
            'product_id' => $this->product->id,
            'cart_color' => 'Gris',
        ], $overrides);
    }

    public function test_seller_observations_and_address_conflict_note_coexist_in_order(): void
    {
        // Cliente ya existente con otra dirección: dispara la nota automática.
        Client::factory()->create([
            'phone' => '3007776655',
            'address' => 'Dirección vieja 11-22',
        ]);

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-payload-both',
            'client_phone' => '3007776655',
            'address' => 'Dirección nueva 33-44',
            'observations' => 'Entregar después de las 3pm',
        ]));

        $response->assertCreated();

        $observations = ProductionOrder::firstOrFail()->observations;

        $this->assertStringStartsWith('Entregar después de las 3pm', $observations);
        $this->assertStringContainsString("\n\n", $observations);
        $this->assertStringContainsString('Dirección distinta reportada por WhatsApp', $observations);
        $this->assertStringContainsString('Dirección nueva 33-44', $observations);
        $this->assertStringContainsString('Dirección vieja 11-22', $observations);
    }

    public function test_seller_observations_alone_are_stored_without_separator(): void
    {
        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-payload-only-obs',
            'observations' => 'Cliente pidió empaque doble',
        ]));

        $response->assertCreated();

        $this->assertSame(
            'Cliente pidió empaque doble',
            ProductionOrder::firstOrFail()->observations,
        );
    }

    public function test_conflict_note_alone_is_stored_without_separator(): void
    {
        Client::factory()->create([
            'phone' => '3007776655',
            'address' => 'Dirección vieja 11-22',
        ]);

        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-payload-only-note',
            'client_phone' => '3007776655',
            'address' => 'Dirección nueva 33-44',
        ]));

        $response->assertCreated();

        $observations = ProductionOrder::firstOrFail()->observations;

        $this->assertStringStartsWith('Dirección distinta reportada por WhatsApp', $observations);
        $this->assertStringNotContainsString("\n\n", $observations);
    }

    public function test_order_without_observations_or_conflict_leaves_the_column_null(): void
    {
        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-payload-no-obs',
        ]));

        $response->assertCreated();
        $this->assertNull(ProductionOrder::firstOrFail()->observations);
    }

    /**
     * "0" es falsy en PHP: con array_filter() a secas, o con un `?: null`
     * al final de la composición, este texto se perdería y la columna
     * quedaría en NULL.
     */
    public function test_a_literal_zero_as_observations_is_not_discarded(): void
    {
        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-payload-zero-obs',
            'observations' => '0',
        ]));

        $response->assertCreated();
        $this->assertSame('0', ProductionOrder::firstOrFail()->observations);
    }

    public function test_observations_over_the_limit_are_rejected(): void
    {
        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-payload-long-obs',
            'observations' => str_repeat('a', 1001),
        ]));

        $response->assertStatus(422);
        $this->assertDatabaseCount('production_orders', 0);
    }

    public function test_advance_payment_method_is_recorded_when_the_seller_dictates_it(): void
    {
        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-payload-nequi',
            'advance_amount' => 100000,
            'payment_method' => 'nequi',
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('payments', [
            'production_order_id' => ProductionOrder::firstOrFail()->id,
            'amount' => 100000,
            'payment_method' => 'nequi',
        ]);
    }

    public function test_advance_payment_method_defaults_to_efectivo_when_absent(): void
    {
        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-payload-default-method',
            'advance_amount' => 100000,
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('payments', [
            'production_order_id' => ProductionOrder::firstOrFail()->id,
            'payment_method' => 'efectivo',
        ]);
    }

    public function test_invalid_payment_method_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/whatsapp/orders', $this->botPayload([
            'idempotency_key' => 'wa-payload-bad-method',
            'advance_amount' => 100000,
            'payment_method' => 'daviplata',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payment_method');
        $this->assertDatabaseCount('production_orders', 0);
    }
}
