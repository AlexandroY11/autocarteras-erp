<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Modules\Dispatch\Services\DispatchService;
use App\Modules\Production\DTOs\ProductionOrderDTO;
use App\Modules\Production\Services\ProductionOrderService;
use App\Services\Mail\MailService;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    public function __construct(
        private ProductionOrderService $service,
        private DispatchService $dispatchService,
    ) {
    }

    public function index(Request $request)
    {
        $query = ProductionOrder::with(['client', 'product', 'currentStage', 'payments', 'orderDispatches']);

        // Filtros existentes
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('consecutive', 'like', "%$search%")
                ->orWhereHas('client', fn($q) => $q->where('full_name', 'like', "%$search%"));
            });
        }

        if ($request->filled('stage')) {
            $query->where('current_stage_id', $request->stage);
        }

        // --- NUEVO: Filtro por Estado de Tiempo ---
        if ($request->filled('time_status')) {
            $status = $request->time_status;
            if ($status === 'overdue') {
                $query->where('due_date', '<', now()->startOfDay());
            } elseif ($status === 'critical') {
                // Lógica: Días restantes < Días promedio del producto
                // (due_date - CURRENT_DATE) es el equivalente nativo de Postgres
                // a DATEDIFF(due_date, NOW()) — diferencia en días completos.
                $query->whereHas('product', function($q) {
                    $q->whereRaw('(production_orders.due_date - CURRENT_DATE) < products.avg_production_days');
                })->where('due_date', '>=', now()->startOfDay());
            }
        }

        // --- NUEVO: Ordenamiento por Prioridad ---
        // 1. Vencidos, 2. Críticos (slack < 0), 3. Próximos (slack <= 2), 4. A tiempo
        $query->orderByRaw("
            CASE
                WHEN due_date < NOW() THEN 1
                WHEN (due_date - CURRENT_DATE) < (SELECT avg_production_days FROM products WHERE id = production_orders.product_id) THEN 2
                WHEN (due_date - CURRENT_DATE) <= (SELECT avg_production_days + 2 FROM products WHERE id = production_orders.product_id) THEN 3
                ELSE 4
            END ASC
        ")->orderBy('due_date', 'asc');

        $orders = $query->paginate(15)->withQueryString();
        $stages = Stage::all();

        return view('orders.index', compact('orders', 'stages'));
    }


    public function create()
    {
        $products = Product::where('active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('orders.form', [
            'order' => new ProductionOrder(),
            'products' => $products,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            // Cliente
            'client_id' => 'nullable|exists:clients,id',
            // Añadimos 'nullable' para que 'string' no falle si el campo llega vacío
            'client_first_name' => 'required_without:client_id|nullable|string|max:100',
            'client_last_name' => 'required_without:client_id|nullable|string|max:100',
            'client_phone' => 'required_without:client_id|nullable|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'client_address' => 'nullable|string|max:255',
            'client_department' => 'nullable|exists:departments,id',
            'client_city' => 'nullable|exists:cities,id',

            // Orden
            'product_id' => 'required|exists:products,id',
            'color' => 'required|string|max:100',
            'sticker' => 'boolean',
            'sticker_color' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'advance_payment' => 'nullable|numeric|min:0',
        ]);

        // NOTA: 'advance_payment' se valida aquí y se usa más abajo para crear
        // el Payment inicial directamente (líneas ~148-159) — no forma parte
        // del DTO/columna de production_orders, que ya no la tiene.
        if ($request->filled('client_id')) {
            $clientId = $request->client_id;
        } else {
            $existingClient = Client::where('phone', $request->client_phone)->first();

            if ($existingClient) {
                return back()->withInput()->withErrors([
                    'client_phone' => "El teléfono ya pertenece a: {$existingClient->first_name} {$existingClient->last_name}. Búscalo en el selector de arriba.",
                ]);
            }

            $client = Client::create([
                'first_name' => $request->client_first_name,
                'last_name' => $request->client_last_name,
                'phone' => $request->client_phone,
                'email' => $request->client_email,
                'address' => $request->client_address,
                'department_id' => $request->client_department,
                'city_id' => $request->client_city,
                'active' => true,
            ]);
            $clientId = $client->id;
        }

        // CREAR DTO Y ORDEN
        $dto = ProductionOrderDTO::fromRequest([
            'client_id' => $clientId,
            'product_id' => $request->product_id,
            'color' => $request->color,
            'sticker' => $request->boolean('sticker'),
            'sticker_color' => $request->sticker_color,
            'observations' => $request->observations,
            'price' => $request->price,
            'due_date' => $request->due_date,
        ]);

        $order = $this->service->create($dto, auth()->id());

        // REGISTRAR ANTICIPO (Si aplica)
        if ($request->filled('advance_payment') && $request->advance_payment > 0) {
            Payment::create([
                'production_order_id' => $order->id,
                'amount' => $request->advance_payment,
                'type' => 'advance',
                'payment_method' => 'efectivo',
                'notes' => 'Anticipo inicial',
                'paid_at' => now()->toDateString(),
                'registered_by' => auth()->id(),
            ]);
        }

        $order->load(['client', 'product', 'payments']);
        $emailSent = MailService::orderCreated($order);

        $msg = 'Orden #'.str_pad($order->consecutive, 3, '0', STR_PAD_LEFT).' creada correctamente.';
        $msg .= $emailSent ? ' Se notificó al cliente por correo.' : ' El cliente no tiene correo registrado.';

        return redirect('/production-orders/'.$order->id)->with('success', $msg);
    }

    public function show(ProductionOrder $productionOrder)
    {
        $order = $productionOrder->load([
            'client', 'product', 'currentStage',
            'orderStages.stage', 'orderStages.assignedTo',
            'payments.registeredBy',
            'orderDispatches',
        ]);

        return view('orders.show', compact('order'));
    }

    public function edit(ProductionOrder $productionOrder)
    {
        $products = Product::where('active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('orders.edit', [
            'order' => $productionOrder,
            'products' => $products,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, ProductionOrder $productionOrder)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'product_id' => 'required|exists:products,id',
            'color' => 'required|string|max:100',
            'sticker' => 'boolean',
            'sticker_color' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'due_date' => 'required|date',
        ]);

        $this->service->update(
            $productionOrder,
            ProductionOrderDTO::fromRequest([
                'client_id' => $request->client_id,
                'product_id' => $request->product_id,
                'color' => $request->color,
                'sticker' => $request->boolean('sticker'),
                'sticker_color' => $request->sticker_color,
                'observations' => $request->observations,
                'price' => $request->price,
                'due_date' => $request->due_date,
            ])
        );

        return redirect('/production-orders/'.$productionOrder->id)
            ->with('success', 'Orden actualizada.');
    }

    public function advanceStage(Request $request, ProductionOrder $productionOrder)
    {
        if (in_array($productionOrder->status, ['done', 'cancelled'])) {
            return back()->withErrors(['error' => 'Esta orden no puede avanzar de etapa.']);
        }

        if ($productionOrder->current_stage_id !== null && $productionOrder->current_stage_id === Stage::enviadoId()) {
            return back()->withErrors([
                'error' => 'La orden ya se encuentra en la etapa Enviado.'
            ]);
        }

        try {
            $this->service->advanceStage(
                $productionOrder,
                auth()->user(),
                $request->input('notes')
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $productionOrder->refresh()->load(['client', 'product', 'currentStage', 'payments']);

        $msg = 'Etapa avanzada correctamente.';

        // Pausado a propósito (decisión del producto, MVP — evitar saturar
        // de correos a un cliente que recién empieza a operar con la app).
        // MailService::orderStageChanged() sigue intacta para reactivar
        // esto más adelante.
        // if ($productionOrder->currentStage) {
        //     $emailSent = MailService::orderStageChanged($productionOrder, $productionOrder->currentStage);
        //     $msg .= $emailSent ? ' Cliente notificado por correo.' : ' El cliente no tiene correo registrado.';
        // }

        return back()->with('success', $msg);
    }
    
    public function cancel(ProductionOrder $productionOrder)
    {
        if (! in_array($productionOrder->dispatch_status, [null, 'pending_dispatch'], true)) {
            return back()->withErrors(['error' => 'No se puede cancelar una orden que ya fue despachada.']);
        }

        $this->service->cancel($productionOrder);

        return redirect('/orders')->with('success', 'Orden cancelada.');
    }

    public function dispatch(Request $request, ProductionOrder $productionOrder)
    {
        $request->validate(['guide_number' => 'nullable|string|max:255']);

        try {
            $dispatch = $this->dispatchService->dispatch($productionOrder, $request->guide_number, auth()->user());
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        $order = $productionOrder->refresh()->load(['client.city', 'client.department', 'product', 'payments']);
        $emailSent = MailService::orderShipped($order, $dispatch);

        $msg = 'Orden despachada correctamente.';
        $msg .= $emailSent ? ' Cliente notificado por correo.' : ' El cliente no tiene correo registrado.';

        return back()->with('success', $msg);
    }

    public function setGuideNumber(Request $request, ProductionOrder $productionOrder)
    {
        $request->validate(['guide_number' => 'required|string|max:255']);

        try {
            $this->dispatchService->setGuideNumber($productionOrder, $request->guide_number);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Número de guía actualizado.');
    }

    public function markSent(ProductionOrder $productionOrder)
    {
        try {
            $this->dispatchService->markSent($productionOrder, auth()->user());
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Orden marcada como en tránsito.');
    }

    public function markDelivered(ProductionOrder $productionOrder)
    {
        try {
            $this->dispatchService->markDelivered($productionOrder, auth()->user());
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Orden marcada como entregada.');
    }

    public function markReturned(Request $request, ProductionOrder $productionOrder)
    {
        $request->validate(['return_reason' => 'required|string']);

        try {
            $this->dispatchService->markReturned($productionOrder, $request->return_reason, auth()->user());
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Orden marcada como devuelta.');
    }

    public function returnToPendingDispatch(ProductionOrder $productionOrder)
    {
        try {
            $this->dispatchService->returnToPendingDispatch($productionOrder);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Orden lista para un nuevo despacho.');
    }

    public function calendar(Request $request)
    {
        $user = auth()->user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $orders = ProductionOrder::with(['product', 'currentStage'])
            ->whereYear('due_date', $year)
            ->whereMonth('due_date', $month)
            // FILTRO: Excluir órdenes canceladas
            ->where('status', '!=', 'cancelled')
            // Worker/Director: solo lo que pueden trabajar según habilidad —
            // mismo filtro que "Mis tareas".
            ->when($user->isOperative(), fn ($q) => $q->whereHas(
                'currentStage', fn ($q) => $q->whereIn('id', $user->skills->pluck('id'))
            ))
            ->get()
            ->groupBy(function($order) {
                return $order->due_date->format('Y-m-d');
            });

        $holidays = Holiday::getHolidayDates($year);

        return view('orders.calendar', compact('orders', 'month', 'year', 'holidays'));
    }

    public function dayDetail($date)
    {
        $user = auth()->user();

        $orders = ProductionOrder::with(['client.city', 'client.department', 'product', 'currentStage', 'payments'])
            ->whereDate('due_date', $date)
            // FILTRO: Excluir órdenes canceladas
            ->where('status', '!=', 'cancelled')
            ->when($user->isOperative(), fn ($q) => $q->whereHas(
                'currentStage', fn ($q) => $q->whereIn('id', $user->skills->pluck('id'))
            ))
            ->get();

        return view('orders.day-detail', compact('orders', 'date'));
    }


}
