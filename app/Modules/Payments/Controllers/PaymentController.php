<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ProductionOrder;
use App\Modules\Payments\DTOs\PaymentDTO;
use App\Modules\Payments\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $service)
    {
    }

    // GET /production-orders/{order}/payments
    public function index(ProductionOrder $productionOrder): JsonResponse
    {
        $payments = $this->service->getByOrder($productionOrder);
        $summary = $this->service->summary($productionOrder);

        return response()->json([
            'summary' => $summary,
            'payments' => $payments,
        ]);
    }

    // POST /payments
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_order_id' => 'required|exists:production_orders,id',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:advance,partial,final',
            'notes' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        try {
            $payment = $this->service->create(
                PaymentDTO::fromRequest($validated),
                $request->user()->id
            );

            return response()->json($payment, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // DELETE /payments/{payment}
    public function destroy(Payment $payment): JsonResponse
    {
        $this->service->delete($payment);

        return response()->json(null, 204);
    }
}
