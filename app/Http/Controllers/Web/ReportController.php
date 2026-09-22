<?php

namespace App\Http\Controllers\Web;

use App\Exports\ClientsReportExport;
use App\Exports\Concerns\HasDueDateTracking;
use App\Exports\FinancialFullReportExport;
use App\Exports\OperationalFollowupReportExport;
use App\Exports\ProductsReportExport;
use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use HasDueDateTracking;

    public function index()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('reports.index');
    }

    public function financialFull(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'date_field' => 'required|in:created_at,delivered_at,production_completed_at',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        return Excel::download(
            new FinancialFullReportExport($validated['date_field'], $validated['from'] ?? null, $validated['to'] ?? null),
            'reporte-financiero-completo-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function operationalFollowup()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return Excel::download(
            new OperationalFollowupReportExport(),
            'reporte-seguimiento-operativo-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function clients(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'city_id' => 'nullable|exists:cities,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'active' => 'nullable|in:0,1',
        ]);

        return Excel::download(
            new ClientsReportExport(
                $validated['department_id'] ?? null,
                $validated['city_id'] ?? null,
                $validated['from'] ?? null,
                $validated['to'] ?? null,
                isset($validated['active']) ? (bool) $validated['active'] : null,
            ),
            'reporte-clientes-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function products()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return Excel::download(
            new ProductsReportExport(),
            'reporte-productos-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * PDF para imprimir en el taller — solo lo que sigue "en casa": todavía
     * no salió hacia el cliente (excluye status done/cancelled y
     * dispatch_status dispatched/sent/delivered/returned).
     */
    public function printProductionList()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $orders = ProductionOrder::with(['client.city', 'client.department', 'product', 'currentStage'])
            ->whereNotIn('status', ['done', 'cancelled'])
            // dispatch_status es NULL hasta que el pedido llega a "pending_dispatch" —
            // whereNotIn() por sí solo excluye los NULL (semántica de 3 valores de
            // SQL), así que hay que incluirlos explícitamente o se pierde casi todo
            // lo que sigue en producción.
            ->where(function ($query) {
                $query->whereNotIn('dispatch_status', ['dispatched', 'sent', 'delivered', 'returned'])
                    ->orWhereNull('dispatch_status');
            })
            ->orderBy('due_date')
            ->get()
            ->map(function (ProductionOrder $order) {
                [$timeStatusLabel, $businessDaysRemaining] = $this->dueDateTrackingColumns($order);

                $order->time_status_summary = $timeStatusLabel
                    ? "{$timeStatusLabel} ({$businessDaysRemaining} días hábiles)"
                    : null;

                return $order;
            });

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.print-production-list', [
            'orders' => $orders,
            'generatedAt' => now(),
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('listado-produccion-' . now()->format('Y-m-d') . '.pdf');
    }
}
