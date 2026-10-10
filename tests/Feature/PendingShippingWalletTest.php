<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\ProductionOrder;
use App\Models\User;
use App\Modules\Dispatch\Services\DispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bolsillo de envíos pendientes: consulta derivada, solo lectura, solo
 * Admin. El filtro es por la bandera histórica del despacho
 * (shipped_with_pending_shipping), no por dispatch_status — justamente para
 * que un devuelto que vuelve a pending_dispatch no desaparezca.
 */
class PendingShippingWalletTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/orders/pending-shipping-wallet';

    private User $admin;

    private DispatchService $dispatchService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->dispatchService = app(DispatchService::class);
    }

    private function orderDispatchedWithUnpaidShipping(array $overrides = []): ProductionOrder
    {
        $order = ProductionOrder::factory()->done()->create(array_merge([
            'price' => 300000,
            'shipping_price' => 20000,
        ], $overrides));

        $this->dispatchService->dispatch($order, null, $this->admin, true);

        return $order->fresh();
    }

    private function walletOrders()
    {
        $response = $this->actingAs($this->admin)->get(self::URL);
        $response->assertOk();

        return $response->viewData('orders');
    }

    public function test_it_lists_an_order_dispatched_with_pending_shipping(): void
    {
        $order = $this->orderDispatchedWithUnpaidShipping();

        $orders = $this->walletOrders();

        $this->assertCount(1, $orders);
        $this->assertSame($order->id, $orders->first()->id);
        $this->assertSame(20000.0, $orders->first()->shipping_balance);
    }

    /**
     * El caso que marcó el dueño del negocio: filtrando por dispatch_status
     * esta orden se caería del bolsillo al volver a pending_dispatch, aunque
     * el envío siga sin pagarse.
     */
    public function test_it_keeps_the_order_after_a_return_and_after_going_back_to_pending_dispatch(): void
    {
        $order = $this->orderDispatchedWithUnpaidShipping();

        $this->dispatchService->markReturned($order, 'Cliente no estaba', $this->admin);
        $this->assertSame('returned', $order->fresh()->dispatch_status);
        $this->assertCount(1, $this->walletOrders(), 'Un devuelto con envío sin pagar debe seguir en el bolsillo.');

        $this->dispatchService->returnToPendingDispatch($order->fresh());
        $this->assertSame('pending_dispatch', $order->fresh()->dispatch_status);
        $this->assertCount(1, $this->walletOrders(), 'Volver a pending_dispatch no debe sacarlo del bolsillo.');
    }

    public function test_a_returned_order_is_shown_with_its_status_label(): void
    {
        $order = $this->orderDispatchedWithUnpaidShipping();
        $this->dispatchService->markReturned($order, 'Cliente no estaba', $this->admin);

        $response = $this->actingAs($this->admin)->get(self::URL);

        $response->assertOk();
        $response->assertSee('Devuelto', false);
    }

    public function test_the_order_leaves_the_wallet_once_the_shipping_is_paid(): void
    {
        $order = $this->orderDispatchedWithUnpaidShipping();

        Payment::factory()->create([
            'production_order_id' => $order->id,
            'amount' => 20000,
            'registered_by' => $this->admin->id,
        ]);

        $this->assertCount(0, $this->walletOrders(), 'Pagado el envío, sale del bolsillo sin intervención manual.');
    }

    public function test_a_partially_paid_shipping_stays_in_the_wallet_with_the_remaining_amount(): void
    {
        $order = $this->orderDispatchedWithUnpaidShipping();

        Payment::factory()->create([
            'production_order_id' => $order->id,
            'amount' => 5000,
            'registered_by' => $this->admin->id,
        ]);

        $orders = $this->walletOrders();

        $this->assertCount(1, $orders);
        $this->assertSame(15000.0, $orders->first()->shipping_balance);
    }

    public function test_it_excludes_cancelled_orders(): void
    {
        $order = $this->orderDispatchedWithUnpaidShipping();

        // Camino real para cancelar algo ya despachado: devolverlo y
        // regresarlo a pending_dispatch (ProductionOrderService::cancel()
        // bloquea cualquier otro dispatch_status).
        $this->dispatchService->markReturned($order, 'Cliente rechazó', $this->admin);
        $this->dispatchService->returnToPendingDispatch($order->fresh());
        $order->fresh()->update(['status' => 'cancelled']);

        $this->assertCount(0, $this->walletOrders());
    }

    /**
     * Doble razón para quedar afuera: shipping_price NULL da
     * shipping_balance = 0 (así que nunca recibe la bandera), y la consulta
     * además filtra whereNotNull('shipping_price') de forma explícita.
     */
    public function test_it_excludes_orders_without_shipping_price(): void
    {
        $this->orderDispatchedWithUnpaidShipping(['shipping_price' => null]);

        $this->assertCount(0, $this->walletOrders());
    }

    public function test_an_order_dispatched_twice_appears_only_once(): void
    {
        $order = $this->orderDispatchedWithUnpaidShipping();

        $this->dispatchService->markReturned($order, 'Primer intento fallido', $this->admin);
        $this->dispatchService->returnToPendingDispatch($order->fresh());
        $this->dispatchService->dispatch($order->fresh(), null, $this->admin, true);

        $this->assertSame(2, $order->orderDispatches()->where('shipped_with_pending_shipping', true)->count());
        $this->assertCount(1, $this->walletOrders(), 'whereHas() es prueba de existencia: no debe duplicar la orden.');
    }

    public function test_an_order_dispatched_with_shipping_paid_never_enters_the_wallet(): void
    {
        $order = ProductionOrder::factory()->done()->create([
            'price' => 300000,
            'shipping_price' => 20000,
        ]);

        Payment::factory()->create([
            'production_order_id' => $order->id,
            'amount' => 20000,
            'registered_by' => $this->admin->id,
        ]);

        $this->dispatchService->dispatch($order->fresh(), null, $this->admin);

        $this->assertCount(0, $this->walletOrders());
    }

    public function test_worker_cannot_access_the_wallet(): void
    {
        $this->orderDispatchedWithUnpaidShipping();

        $this->actingAs(User::factory()->worker()->create())
            ->get(self::URL)
            ->assertForbidden();
    }

    public function test_director_cannot_access_the_wallet(): void
    {
        $this->orderDispatchedWithUnpaidShipping();

        $this->actingAs(User::factory()->director()->create())
            ->get(self::URL)
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(self::URL)->assertRedirect('/login');
    }
}
