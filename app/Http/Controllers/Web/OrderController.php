<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\Stage;

class OrderController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Worker y Director: misma vista, mismo filtro por habilidad (solo
        // etapas que el usuario puede avanzar) — swipe-to-advance, con
        // cliente/ciudad visibles pero sin nada financiero (sección 21).
        if ($user->isOperative()) {
            $myOrders = ProductionOrder::with(['client.city', 'client.department', 'product', 'currentStage'])
                ->whereNotIn('status', ['done', 'cancelled'])
                ->whereHas('currentStage', fn ($q) => $q->whereIn('id', $user->skills->pluck('id')))
                ->search(request('search'))
                ->orderBy('due_date')
                ->get();

            return view('orders.my-tasks', compact('myOrders'));
        }

        // Admin ve todo
        $stages = Stage::where('active', true)->orderBy('order')->get();

        $orders = ProductionOrder::with(['client.city', 'client.department', 'product', 'currentStage', 'payments'])
            ->where('status', '!=', 'cancelled')
            ->where(fn ($q) => $q->whereNull('dispatch_status')->orWhere('dispatch_status', '!=', 'delivered'))
            ->when(request('stage'), fn ($q, $s) => $q->where('current_stage_id', $s))
            ->search(request('search'))
            ->timeStatus(request('time_status'))
            ->orderByDesc('consecutive')
            ->paginate(20);

        return view('orders.index', compact('orders', 'stages'));
    }

    /**
     * Bolsillo de envíos pendientes — solo lectura, solo Admin. Es una
     * consulta derivada, sin tabla ni estado propio: un pedido entra cuando
     * salió despachado con el envío sin pagar, y sale solo en cuanto se
     * registre el pago (shipping_balance se recalcula desde los pagos cada
     * vez, no hay nada que marcar a mano).
     *
     * El filtro es por la bandera histórica del despacho, no por
     * dispatch_status, a propósito: un pedido devuelto que vuelve a
     * pending_dispatch con el envío sin pagar debe seguir en el bolsillo, y
     * filtrando por estado desaparecería. whereHas() es una prueba de
     * existencia, así que un pedido redespachado dos veces aparece una vez.
     *
     * shipping_balance es un accessor (lo calcula PaymentAllocationService),
     * no una columna, así que el último filtro va en PHP: replicarlo en SQL
     * duplicaría la regla de "el envío se cubre primero".
     */
    public function pendingShippingWallet()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $orders = ProductionOrder::with(['client', 'payments', 'orderDispatches'])
            ->whereNotNull('shipping_price')
            ->where('status', '!=', 'cancelled')
            ->whereHas('orderDispatches', fn ($q) => $q->where('shipped_with_pending_shipping', true))
            ->orderByDesc('consecutive')
            ->get()
            ->filter(fn (ProductionOrder $order) => $order->shipping_balance > 0)
            ->values();

        return view('orders.pending-shipping-wallet', compact('orders'));
    }

    public function show(ProductionOrder $productionOrder)
    {
        return redirect("/production-orders/{$productionOrder->id}");
    }
}
