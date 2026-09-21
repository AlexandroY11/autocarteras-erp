<?php

namespace App\Modules\Dispatch\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductionOrder;
use App\Modules\Dispatch\Services\DispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DispatchController extends Controller
{
    public function __construct(private DispatchService $service) {}

    public function dispatch(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $validated = $request->validate([
            'guide_number' => 'nullable|string|max:255',
        ]);

        try {
            $dispatch = $this->service->dispatch($productionOrder, $validated['guide_number'] ?? null, $request->user());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }

        return response()->json($dispatch, 201);
    }

    public function setGuideNumber(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $validated = $request->validate([
            'guide_number' => 'required|string|max:255',
        ]);

        try {
            $dispatch = $this->service->setGuideNumber($productionOrder, $validated['guide_number']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }

        return response()->json($dispatch);
    }

    public function markSent(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        try {
            $dispatch = $this->service->markSent($productionOrder, $request->user());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }

        return response()->json($dispatch);
    }

    public function markDelivered(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        try {
            $dispatch = $this->service->markDelivered($productionOrder, $request->user());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }

        return response()->json($dispatch);
    }

    public function markReturned(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        $validated = $request->validate([
            'return_reason' => 'required|string',
        ]);

        try {
            $dispatch = $this->service->markReturned($productionOrder, $validated['return_reason'], $request->user());
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }

        return response()->json($dispatch);
    }

    public function returnToPendingDispatch(ProductionOrder $productionOrder): JsonResponse
    {
        try {
            $order = $this->service->returnToPendingDispatch($productionOrder);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }

        return response()->json($order);
    }
}
