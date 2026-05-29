<?php

namespace App\Modules\Production\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Modules\Production\DTOs\ProductionOrderDTO;
use App\Modules\Production\Services\ProductionOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
    public function __construct(private ProductionOrderService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'product_id'      => 'required|exists:products,id',
            'color'           => 'required|string|max:100',
            'sticker'         => 'boolean',
            'sticker_color'   => 'nullable|string|max:100',
            'observations'    => 'nullable|string',
            'price'           => 'required|numeric|min:0',
            'advance_payment' => 'nullable|numeric|min:0',
            'due_date'        => 'required|date|after_or_equal:today',
        ]);

        $order = $this->service->create(
            ProductionOrderDTO::fromRequest($validated),
            $request->user()->id
        );

        return response()->json($order, 201);
    }

    public function show(ProductionOrder $productionOrder): JsonResponse
    {
        return response()->json(
            $productionOrder->load(['client', 'product', 'currentStage', 'orderStages.stage', 'orderStages.assignedTo', 'payments'])
        );
    }

    public function update(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $validated = $request->validate([
            'client_id'       => 'sometimes|required|exists:clients,id',
            'product_id'      => 'sometimes|required|exists:products,id',
            'color'           => 'sometimes|required|string|max:100',
            'sticker'         => 'boolean',
            'sticker_color'   => 'nullable|string|max:100',
            'observations'    => 'nullable|string',
            'price'           => 'sometimes|required|numeric|min:0',
            'advance_payment' => 'nullable|numeric|min:0',
            'due_date'        => 'sometimes|required|date',
        ]);

        $order = $this->service->update(
            $productionOrder,
            ProductionOrderDTO::fromRequest($validated)
        );

        return response()->json($order);
    }

    public function advanceStage(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        if (in_array($productionOrder->status, ['done', 'delivered', 'cancelled'])) {
            return response()->json([
                'message' => 'Esta orden no puede avanzar de etapa.'
            ], 422);
        }

        $order = $this->service->advanceStage(
            $productionOrder,
            $request->user()->id,
            $validated['notes'] ?? null
        );

        return response()->json($order);
    }

    public function cancel(ProductionOrder $productionOrder): JsonResponse
    {
        if ($productionOrder->status === 'delivered') {
            return response()->json([
                'message' => 'No se puede cancelar una orden ya entregada.'
            ], 422);
        }

        return response()->json($this->service->cancel($productionOrder));
    }

    public function destroy(ProductionOrder $productionOrder): JsonResponse
    {
        $this->service->delete($productionOrder);
        return response()->json(null, 204);
    }
}