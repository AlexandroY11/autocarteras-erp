<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Regla 16, cambiada por decisión del dueño del negocio: un saldo de envío
 * pendiente ya no bloquea el despacho de forma absoluta, pero exige
 * confirmación explícita. La regla vive solo en DispatchService, así que
 * estas pruebas la ejercen por los dos canales (web y API) para verificar
 * que ninguno la reimplementa ni la saltea.
 */
class DispatchWithPendingShippingTest extends TestCase
{
    use RefreshDatabase;

    private function orderReadyToDispatch(array $overrides = []): ProductionOrder
    {
        // Producto 300.000 + envío 20.000, sin pagos: shipping_balance = 20.000.
        return ProductionOrder::factory()->done()->create(array_merge([
            'price' => 300000,
            'shipping_price' => 20000,
        ], $overrides));
    }

    public function test_web_dispatch_is_rejected_without_confirmation_when_shipping_is_unpaid(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->orderReadyToDispatch();

        $response = $this->actingAs($admin)
            ->from("/production-orders/{$order->id}")
            ->post("/production-orders/{$order->id}/dispatch");

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseCount('order_dispatches', 0);
        $this->assertSame('pending_dispatch', $order->fresh()->dispatch_status);
    }

    public function test_web_dispatch_proceeds_with_confirmation_and_records_the_pending_shipping(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->orderReadyToDispatch();

        $response = $this->actingAs($admin)
            ->from("/production-orders/{$order->id}")
            ->post("/production-orders/{$order->id}/dispatch", [
                'confirm_unpaid_shipping' => 1,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('dispatched', $order->fresh()->dispatch_status);

        $dispatch = $order->fresh()->latestDispatch();
        $this->assertTrue($dispatch->shipped_with_pending_shipping);
        $this->assertSame('20000.00', $dispatch->pending_shipping_amount);
        $this->assertSame($admin->id, $dispatch->dispatched_by);
    }

    public function test_dispatch_with_shipping_already_paid_records_no_pending_flag(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->orderReadyToDispatch();

        // El pago cubre primero el envío (PaymentAllocationService), así que
        // 20.000 dejan shipping_balance en 0 y saldo de producto pendiente.
        Payment::factory()->create([
            'production_order_id' => $order->id,
            'amount' => 20000,
            'registered_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->from("/production-orders/{$order->id}")
            ->post("/production-orders/{$order->id}/dispatch");

        $response->assertSessionHasNoErrors();

        $dispatch = $order->fresh()->latestDispatch();
        $this->assertFalse($dispatch->shipped_with_pending_shipping);
        $this->assertNull($dispatch->pending_shipping_amount);
    }

    public function test_api_dispatch_is_rejected_without_confirmation_when_shipping_is_unpaid(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create(), ['*']);
        $order = $this->orderReadyToDispatch();

        $response = $this->postJson("/api/v1/production-orders/{$order->id}/dispatch");

        $response->assertStatus(422);
        $this->assertDatabaseCount('order_dispatches', 0);
        $this->assertSame('pending_dispatch', $order->fresh()->dispatch_status);
    }

    public function test_api_dispatch_proceeds_with_confirmation_and_records_the_pending_shipping(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create(), ['*']);
        $order = $this->orderReadyToDispatch();

        $response = $this->postJson("/api/v1/production-orders/{$order->id}/dispatch", [
            'confirm_unpaid_shipping' => true,
        ]);

        $response->assertCreated();

        $dispatch = $order->fresh()->latestDispatch();
        $this->assertTrue($dispatch->shipped_with_pending_shipping);
        $this->assertSame('20000.00', $dispatch->pending_shipping_amount);
    }

    /**
     * shipping_price = NULL sigue significando "no definido", no "debe":
     * PaymentAllocationService lo trata como 0, así que shipping_balance
     * nunca da > 0 y el despacho no pide confirmación ninguna.
     */
    public function test_an_order_without_shipping_price_dispatches_without_asking_for_confirmation(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->orderReadyToDispatch(['shipping_price' => null]);

        $response = $this->actingAs($admin)
            ->from("/production-orders/{$order->id}")
            ->post("/production-orders/{$order->id}/dispatch");

        $response->assertSessionHasNoErrors();

        $dispatch = $order->fresh()->latestDispatch();
        $this->assertFalse($dispatch->shipped_with_pending_shipping);
        $this->assertNull($dispatch->pending_shipping_amount);
    }

    public function test_the_detail_view_offers_the_confirmation_form_when_shipping_is_unpaid(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->orderReadyToDispatch();

        $response = $this->actingAs($admin)->get("/production-orders/{$order->id}");

        $response->assertOk();
        $response->assertSee('name="confirm_unpaid_shipping"', false);
        $response->assertSee('El envío todavía no está pagado', false);
        $response->assertSee('Despachar', false);
    }

    public function test_the_detail_view_has_no_confirmation_input_when_shipping_is_paid(): void
    {
        $admin = User::factory()->admin()->create();
        $order = $this->orderReadyToDispatch();

        Payment::factory()->create([
            'production_order_id' => $order->id,
            'amount' => 20000,
            'registered_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get("/production-orders/{$order->id}");

        $response->assertOk();
        $response->assertDontSee('name="confirm_unpaid_shipping"', false);
        $response->assertSee('Despachar', false);
    }
}
