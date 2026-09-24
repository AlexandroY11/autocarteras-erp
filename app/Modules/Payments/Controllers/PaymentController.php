<?php

namespace App\Modules\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
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

    // GET /production-orders/{order}/payments — 100% financiero por diseño
    // (montos, saldos), así que sin Admin no hay nada útil que devolver aquí
    // (ver docs/business-rules/01-roles-y-permisos.md: Director/Worker sin
    // acceso financiero) — se oculta por completo en vez de dejar filtrar
    // el saldo/estado de pago a través de un campo suelto.
    public function index(Request $request, ProductionOrder $productionOrder): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json([
                'summary' => null,
                'payments' => [],
            ]);
        }

        $payments = $this->service->getByOrder($productionOrder);
        $summary = $this->service->summary($productionOrder);

        return response()->json([
            'summary' => $summary,
            'payments' => PaymentResource::collection($payments),
        ]);
    }

    // POST /payments
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_order_id' => 'required|exists:production_orders,id',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:advance,partial,final',
            'payment_method' => 'required|in:efectivo,nequi,nu',
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
}
