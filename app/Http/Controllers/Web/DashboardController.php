<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ProductionOrder;
use App\Models\Stage;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        // Estadísticas generales
        $stats = [
            'pending' => ProductionOrder::where('status', 'pending')->count(),
            'in_progress' => ProductionOrder::where('status', 'in_progress')->count(),
            'done' => ProductionOrder::where('status', 'done')->count(),
            'delivered' => ProductionOrder::where('status', 'delivered')->count(),
            'overdue' => ProductionOrder::whereNotIn('status', ['done', 'delivered', 'cancelled'])
                                ->where('due_date', '<', today())->count(),
        ];

        // Dinero del mes
        $monthlyRevenue = Payment::whereMonth('paid_at', $month)
            ->whereYear('paid_at', $year)
            ->sum('amount');

        // Órdenes del mes
        $monthlyOrders = ProductionOrder::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();

        // Saldo pendiente total
        $totalPending = ProductionOrder::whereNotIn('status', ['cancelled'])
            ->get()
            ->sum(fn ($o) => max(0, $o->price - $o->payments->sum('amount')));

        // Top ciudades
        $topCities = ProductionOrder::join('clients', 'clients.id', '=', 'production_orders.client_id')
            ->selectRaw('clients.city, count(*) as total')
            ->whereNotNull('clients.city')
            ->groupBy('clients.city')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'city');

        // Órdenes por etapa
        $byStage = Stage::where('active', true)
            ->orderBy('order')
            ->withCount(['productionOrders as orders_count' => fn ($q) => $q->whereNotIn('status', ['done', 'delivered', 'cancelled']),
            ])
            ->get();

        // Órdenes vencidas
        $overdueOrders = ProductionOrder::with(['client', 'product', 'currentStage'])
            ->whereNotIn('status', ['done', 'delivered', 'cancelled'])
            ->where('due_date', '<', today())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats', 'monthlyRevenue', 'monthlyOrders',
            'totalPending', 'topCities', 'byStage', 'overdueOrders'
        ));
    }
}
