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
            'pending' => ProductionOrder::where('status', 'pending')->count(),
            'in_progress' => ProductionOrder::where('status', 'in_progress')->count(),
            'done' => ProductionOrder::where('status', 'done')->count(),
            'delivered' => ProductionOrder::where('status', 'delivered')->count(),
        ];

        $orders = ProductionOrder::with(['client', 'product', 'currentStage'])
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->orderByDesc('consecutive')
            ->paginate(20);

        $stages = Stage::where('active', true)->orderBy('order')->get();

        return view('dashboard', compact('stats', 'orders', 'stages'));
    }
}
