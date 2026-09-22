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
            ->when(request('search'), fn ($q, $s) => $q->whereHas('client', fn ($q) => $q->where('first_name', 'ilike', "%{$s}%")
                      ->orWhere('last_name', 'ilike', "%{$s}%")
            )->orWhere('consecutive', 'like', "%{$s}%")
            )
            ->orderByDesc('consecutive')
            ->paginate(20);

        return view('orders.index', compact('orders', 'stages'));
    }

    public function show(ProductionOrder $productionOrder)
    {
        return redirect("/production-orders/{$productionOrder->id}");
    }
}
