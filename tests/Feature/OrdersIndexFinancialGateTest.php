<?php

namespace Tests\Feature;

use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * orders/index.blade.php hoy solo la renderiza Admin (Worker y Director
 * tienen sus propias vistas — ver WorkerOrdersViewTest). El gate financiero
 * aquí es defensivo: protege el dato, no "quién llega a la ruta hoy", para
 * que un cambio futuro de branding en OrderController::index() no reabra
 * la fuga de la sección 21 sin que nadie se dé cuenta.
 */
class OrdersIndexFinancialGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_still_sees_price_and_balance_on_orders_index(): void
    {
        $admin = User::factory()->admin()->create();
        $order = ProductionOrder::factory()->create(); // sin pagos -> balance > 0 -> "Debe $"

        $response = $this->actingAs($admin)->get('/orders');

        $response->assertOk();
        $response->assertViewIs('orders.index');
        $response->assertSee('Debe $' . number_format($order->price, 0, ',', '.'), false);
    }

    /**
     * Nadie llega hoy a esta vista siendo no-admin (OrderController::index()
     * ya desvía a Worker/Director antes), pero el gate del Blade debe
     * funcionar igual si algún día ese branding cambia. Renderiza la vista
     * directamente para probar el gate en sí, no el routing de hoy.
     */
    public function test_price_and_balance_block_is_hidden_for_non_admin_if_this_view_is_ever_rendered_for_them(): void
    {
        $worker = User::factory()->worker()->create();
        $order = ProductionOrder::factory()->create();
        $orders = ProductionOrder::query()->paginate(20);
        $stages = \App\Models\Stage::all();

        $this->actingAs($worker);
        View::share('errors', new \Illuminate\Support\ViewErrorBag());
        $html = view('orders.index', compact('orders', 'stages'))->render();

        $this->assertStringNotContainsString('Pagado', $html);
        $this->assertStringNotContainsString('Debe $', $html);
    }
}
