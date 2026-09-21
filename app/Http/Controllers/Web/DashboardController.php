<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ProductionOrder;
use App\Models\Stage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;
        $user = auth()->user();

        // Estadísticas generales
        $stats = [
            'pending' => ProductionOrder::where('status', 'pending')->count(),
            'in_progress' => ProductionOrder::where('status', 'in_progress')->count(),
            'done' => ProductionOrder::where('status', 'done')->count(),
            'delivered' => ProductionOrder::where('dispatch_status', 'delivered')->count(),
            'overdue' => ProductionOrder::whereNotIn('status', ['done', 'cancelled'])
                                ->where('due_date', '<', today())->count(),
        ];

        // Información financiera global — solo Admin (sección 21: Worker/Director
        // no deben ver esto). No se calcula siquiera si no aplica, no es solo
        // un @if en la vista.
        $monthlyRevenue = null;
        $totalPending = null;

        if ($user->isAdmin()) {
            // Dinero del mes
            $monthlyRevenue = Payment::whereMonth('paid_at', $month)
                ->whereYear('paid_at', $year)
                ->sum('amount');

            // Saldo pendiente total (envío + producto, vía el motor de distribución)
            $totalPending = ProductionOrder::whereNotIn('status', ['cancelled'])
                ->with('payments')
                ->get()
                ->sum(fn ($o) => max(0, $o->total_balance));
        }

        // Órdenes del mes
        $monthlyOrders = ProductionOrder::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();

        // Órdenes en etapas que el usuario logueado puede trabajar (por
        // habilidad, user_skills) — aproximado, no es una asignación personal:
        // si dos workers comparten habilidad, ambos ven el mismo número.
        $workableStageIds = $user->skills()->pluck('stages.id');
        $hasWorkableSkills = $workableStageIds->isNotEmpty();
        $myWorkableOrders = $hasWorkableSkills
            ? ProductionOrder::with(['client', 'product', 'currentStage'])
                ->whereIn('current_stage_id', $workableStageIds)
                ->whereNotIn('status', ['done', 'cancelled'])
                ->orderBy('due_date')
                ->get()
            : collect();

        // Top ciudades (Actualizado para unir con la tabla 'cities')
        $topCities = ProductionOrder::join('clients', 'clients.id', '=', 'production_orders.client_id')
            ->join('cities', 'cities.id', '=', 'clients.city_id')
            ->selectRaw('cities.name as city_name, count(*) as total')
            ->groupBy('cities.id', 'cities.name')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'city_name');

        // Órdenes por etapa
        $byStage = Stage::where('active', true)
            ->orderBy('order')
            ->withCount(['productionOrders as orders_count' => fn ($q) => $q->whereNotIn('status', ['done', 'cancelled']),
            ])
            ->get();

        // Órdenes vencidas (Cargamos client.city y client.department para la vista)
        $overdueOrders = ProductionOrder::with(['client.city', 'client.department', 'product', 'currentStage'])
            ->whereNotIn('status', ['done', 'cancelled'])
            ->where('due_date', '<', today())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats', 'monthlyRevenue', 'monthlyOrders',
            'totalPending', 'topCities', 'byStage', 'overdueOrders',
            'myWorkableOrders', 'hasWorkableSkills'
        ));
    }
}
