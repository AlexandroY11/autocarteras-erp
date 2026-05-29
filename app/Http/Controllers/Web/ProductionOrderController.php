<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Modules\Production\DTOs\ProductionOrderDTO;
use App\Modules\Production\Services\ProductionOrderService;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    public function __construct(private ProductionOrderService $service)
    {
    }

    public function index()
    {
        return redirect('/dashboard');
    }

    public function create()
    {
        $clients = Client::where('active', true)->orderBy('first_name')->get();
        $products = Product::where('active', true)->orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();

        return view('orders.form', [
            'order' => new ProductionOrder(),
            'clients' => $clients,
            'products' => $products,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            // Cliente existente o nuevo
            'client_id' => 'nullable|exists:clients,id',
            'client_first_name' => 'required_without:client_id|string|max:100',
            'client_last_name' => 'required_without:client_id|string|max:100',
            'client_phone' => 'required_without:client_id|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'client_city' => 'nullable|string|max:100',
            'client_department' => 'nullable|string|max:100',
            // Orden
            'product_id' => 'required|exists:products,id',
            'color' => 'required|string|max:100',
            'sticker' => 'boolean',
            'sticker_color' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            // Pago inicial
            'advance_payment' => 'nullable|numeric|min:0',
        ]);

        // Crear cliente si no existe
        if ($request->filled('client_id')) {
            $clientId = $request->client_id;
        } else {
            $client = Client::create([
                'first_name' => $request->client_first_name,
                'last_name' => $request->client_last_name,
                'phone' => $request->client_phone,
                'email' => $request->client_email,
                'city' => $request->client_city,
                'department' => $request->client_department,
                'active' => true,
            ]);
            $clientId = $client->id;
        }

        $dto = ProductionOrderDTO::fromRequest([
            'client_id' => $clientId,
            'product_id' => $request->product_id,
            'color' => $request->color,
            'sticker' => $request->boolean('sticker'),
            'sticker_color' => $request->sticker_color,
            'observations' => $request->observations,
            'price' => $request->price,
            'advance_payment' => $request->advance_payment ?? 0,
            'due_date' => $request->due_date,
        ]);

        $order = $this->service->create($dto, auth()->id());

        // Registrar anticipo si viene
        if ($request->filled('advance_payment') && $request->advance_payment > 0) {
            \App\Models\Payment::create([
                'production_order_id' => $order->id,
                'amount' => $request->advance_payment,
                'type' => 'advance',
                'notes' => 'Anticipo inicial',
                'paid_at' => now()->toDateString(),
                'registered_by' => auth()->id(),
            ]);
        }

        return redirect('/production-orders/'.$order->id)
            ->with('success', 'Orden #'.str_pad($order->consecutive, 3, '0', STR_PAD_LEFT).' creada correctamente.');
    }

    public function show(ProductionOrder $productionOrder)
    {
        $order = $productionOrder->load([
            'client', 'product', 'currentStage',
            'orderStages.stage', 'orderStages.assignedTo',
            'payments.registeredBy',
        ]);

        return view('orders.show', compact('order'));
    }

    public function edit(ProductionOrder $productionOrder)
    {
        $clients = Client::where('active', true)->orderBy('first_name')->get();
        $products = Product::where('active', true)->orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();

        return view('orders.form', [
            'order' => $productionOrder,
            'clients' => $clients,
            'products' => $products,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, ProductionOrder $productionOrder)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'product_id' => 'required|exists:products,id',
            'color' => 'required|string|max:100',
            'sticker' => 'boolean',
            'sticker_color' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'advance_payment' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
        ]);

        $validated['sticker'] = $request->boolean('sticker');

        $this->service->update(
            $productionOrder,
            ProductionOrderDTO::fromRequest($validated)
        );

        return redirect('/dashboard')->with('success', 'Orden actualizada.');
    }

    public function destroy(ProductionOrder $productionOrder)
    {
        $productionOrder->delete();

        return redirect('/dashboard')->with('success', 'Orden eliminada.');
    }

    public function advanceStage(Request $request, ProductionOrder $productionOrder)
    {
        if (in_array($productionOrder->status, ['done', 'delivered', 'cancelled'])) {
            return back()->withErrors(['error' => 'Esta orden no puede avanzar de etapa.']);
        }

        $this->service->advanceStage(
            $productionOrder,
            auth()->id(),
            $request->input('notes')
        );

        return back()->with('success', 'Etapa avanzada correctamente.');
    }

    public function cancel(ProductionOrder $productionOrder)
    {
        if ($productionOrder->status === 'delivered') {
            return back()->withErrors(['error' => 'No se puede cancelar una orden ya entregada.']);
        }

        $this->service->cancel($productionOrder);

        return redirect('/dashboard')->with('success', 'Orden cancelada.');
    }
}
