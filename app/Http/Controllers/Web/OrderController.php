<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\Stage;

class OrderController extends Controller
{
    public function index()
    {
        $stages = Stage::where('active', true)->orderBy('order')->get();

        $orders = ProductionOrder::with(['client', 'product', 'currentStage'])
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->when(request('stage'), fn ($q, $s) => $q->where('current_stage_id', $s))
            ->when(request('search'), fn ($q, $s) => $q->whereHas('client', fn ($q) => $q->where('first_name', 'ilike', "%{$s}%")
                      ->orWhere('last_name', 'ilike', "%{$s}%")
            )->orWhere('consecutive', 'like', "%{$s}%")
            )
            ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('consecutive')
            ->paginate(20);

        return view('orders.index', compact('orders', 'stages'));
    }

    public function show(ProductionOrder $productionOrder)
    {
        return redirect("/production-orders/{$productionOrder->id}");
    }
}
