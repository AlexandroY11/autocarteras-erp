<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Department;
use App\Models\Payment;
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
        return redirect('/orders');
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
            'advance_payment' => $request->advance_payment ?? 0,
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
        $products = Product::where('active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('orders.form', [
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
            'advance_payment' => 'nullable|numeric|min:0',
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
                'advance_payment' => $request->advance_payment ?? 0,
                'due_date' => $request->due_date,
            ])
        );

        return redirect('/production-orders/'.$productionOrder->id)
            ->with('success', 'Orden actualizada.');
    }

    public function destroy(ProductionOrder $productionOrder)
    {
        $productionOrder->delete();

        return redirect('/orders')->with('success', 'Orden eliminada.');
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

        return redirect('/orders')->with('success', 'Orden cancelada.');
    }
}
