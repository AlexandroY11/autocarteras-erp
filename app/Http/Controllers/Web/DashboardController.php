<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Models\Stage;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'pending'     => ProductionOrder::where('status', 'pending')->count(),
            'in_progress' => ProductionOrder::where('status', 'in_progress')->count(),
            'done'        => ProductionOrder::where('status', 'done')->count(),
            'delivered'   => ProductionOrder::where('status', 'delivered')->count(),
        ];

        $orders = ProductionOrder::with(['client', 'product', 'currentStage'])
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->when(request('stage'), fn($q, $s) =>
                $q->where('current_stage_id', $s)
            )
            ->when(request('search'), fn($q, $s) =>
                $q->whereHas('client', fn($q) =>
                    $q->where('first_name', 'ilike', "%{$s}%")
                      ->orWhere('last_name', 'ilike', "%{$s}%")
                )->orWhere('consecutive', 'like', "%{$s}%")
            )
            ->orderByDesc('consecutive')
            ->paginate(20);

        $stages = Stage::where('active', true)->orderBy('order')->get();

        return view('dashboard', compact('stats', 'orders', 'stages'));
    }
}