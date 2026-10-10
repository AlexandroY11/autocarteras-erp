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
 * Regresión: ni el formulario de edición ni el endpoint de actualización
 * incluyen seller_id, origin ni idempotency_key al armar el
 * ProductionOrderDTO, así que toArray() aporta sus valores por defecto
 * (null / 'manual'). Sin la protección de
 * ProductionOrderService::CREATION_ONLY_FIELDS, editar cualquier campo del
 * pedido los pisaba en silencio:
 *
 * - se perdía el vendedor,
 * - un pedido del bot pasaba a figurar como creado a mano,
 * - y, lo más grave, se borraba la idempotency_key: un reintento de n8n con
 *   esa misma key ya no encontraría la orden y crearía un duplicado.
 */
class OrderUpdatePreservesCreationFieldsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $seller;

    private City $city;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Stage::factory()->create(['order' => 1]);

        $this->admin = User::factory()->admin()->create();
        $this->seller = User::factory()->director()->create();

        $department = Department::factory()->create();
        $this->city = City::factory()->create(['department_id' => $department->id]);
        $this->product = Product::factory()->create(['active' => true]);
    }

    private function orderFromTheBot(): ProductionOrder
    {
        return ProductionOrder::factory()->create([
            'client_id' => Client::factory()->create([
                'city_id' => $this->city->id,
                'department_id' => $this->city->department_id,
            ])->id,
            'product_id' => $this->product->id,
            'price' => 300000,
            'seller_id' => $this->seller->id,
            'origin' => 'whatsapp',
            'idempotency_key' => 'wa-key-no-se-toca',
        ]);
    }

    private function editPayload(ProductionOrder $order, array $overrides = []): array
    {
        return array_merge([
            'client_id' => $order->client_id,
            'product_id' => $order->product_id,
            'color' => 'Negro',
            'sticker' => false,
            'observations' => 'Editado desde el formulario',
            'price' => 350000,
            'due_date' => now()->addDays(20)->toDateString(),
        ], $overrides);
    }

    public function test_the_web_edit_form_does_not_wipe_seller_origin_or_idempotency_key(): void
    {
        $order = $this->orderFromTheBot();

        $response = $this->actingAs($this->admin)
            ->put("/production-orders/{$order->id}", $this->editPayload($order));

        $response->assertRedirect();

        $fresh = $order->fresh();

        // Lo que la edición sí debe cambiar:
        $this->assertSame('Negro', $fresh->color);
        $this->assertSame('Editado desde el formulario', $fresh->observations);
        $this->assertSame('350000.00', $fresh->price);

        // Lo que jamás debe tocar:
        $this->assertSame($this->seller->id, $fresh->seller_id, 'La edición no debe borrar el vendedor.');
        $this->assertSame('whatsapp', $fresh->origin, 'La edición no debe convertir un pedido del bot en manual.');
        $this->assertSame('wa-key-no-se-toca', $fresh->idempotency_key, 'Borrar la idempotency_key permitiría un pedido duplicado.');
    }

    public function test_the_api_update_endpoint_does_not_wipe_them_either(): void
    {
        Sanctum::actingAs($this->admin, ['*']);
        $order = $this->orderFromTheBot();

        $response = $this->putJson("/api/v1/production-orders/{$order->id}", [
            'color' => 'Negro',
            'price' => 350000,
        ]);

        $response->assertOk();

        $fresh = $order->fresh();

        $this->assertSame('Negro', $fresh->color);
        $this->assertSame($this->seller->id, $fresh->seller_id);
        $this->assertSame('whatsapp', $fresh->origin);
        $this->assertSame('wa-key-no-se-toca', $fresh->idempotency_key);
    }

    /**
     * Un pedido manual sin vendedor tampoco debe quedar "arreglado" por la
     * edición: NULL significa "no definido" y así se queda.
     */
    public function test_a_manual_order_keeps_its_null_seller_and_manual_origin(): void
    {
        $order = ProductionOrder::factory()->create([
            'client_id' => Client::factory()->create([
                'city_id' => $this->city->id,
                'department_id' => $this->city->department_id,
            ])->id,
            'product_id' => $this->product->id,
            'price' => 300000,
            'seller_id' => null,
            'origin' => 'manual',
            'idempotency_key' => null,
        ]);

        $this->actingAs($this->admin)
            ->put("/production-orders/{$order->id}", $this->editPayload($order))
            ->assertRedirect();

        $fresh = $order->fresh();

        $this->assertNull($fresh->seller_id);
        $this->assertSame('manual', $fresh->origin);
        $this->assertNull($fresh->idempotency_key);
        $this->assertSame('Negro', $fresh->color);
    }
}
