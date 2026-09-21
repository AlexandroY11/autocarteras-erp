<?php

namespace App\Modules\Payments\Services;

use App\Models\Payment;
use App\Models\ProductionOrder;
use App\Modules\Payments\DTOs\PaymentDTO;
use Illuminate\Database\Eloquent\Collection;

class PaymentService
{
    public function getByOrder(ProductionOrder $order): Collection
    {
        return $order->payments()
            ->with('registeredBy')
            ->orderByDesc('paid_at')
            ->get()
            ->each(fn ($p) => $p->append([]));
    }

    /**
     * NOTA (decisión consciente, Etapa 2 - Payments): este método NO envía
     * ningún correo al cliente. La notificación por email de un pago
     * registrado sigue siendo responsabilidad exclusiva del canal Web
     * (ver App\Http\Controllers\Web\PaymentController::store()), porque
     * hoy no existe ningún consumidor real escribiendo pagos vía esta API
     * (n8n solo tiene la ability 'payments:read'). Cuando exista un
     * consumidor real de escritura por API, se debe decidir explícitamente
     * si también debe notificar — no asumirlo por defecto en ese momento.
     */
    public function create(PaymentDTO $dto, int $userId): Payment
    {
        $order = ProductionOrder::findOrFail($dto->production_order_id);

        // Validar que no se pague más de lo que falta (envío + producto)
        $totalBalance = $order->total_balance;

        if ($dto->amount > $totalBalance) {
            throw new \Exception("El pago ({$dto->amount}) supera el saldo pendiente ({$totalBalance}).", 422);
        }

        $payment = Payment::create([
            ...$dto->toArray(),
            'registered_by' => $userId,
        ]);

        return $payment->load('registeredBy');
    }

    public function summary(ProductionOrder $order): array
    {
        $breakdown = $order->paymentBreakdown();

        return [
            'price' => $breakdown->productPrice,
            'shipping_price' => $breakdown->shippingPrice,
            'total_paid' => $breakdown->totalPaid,
            'shipping_paid' => $breakdown->shippingPaid,
            'shipping_balance' => $breakdown->shippingBalance,
            'product_paid' => $breakdown->productPaid,
            'product_balance' => $breakdown->productBalance,
            'total_balance' => $breakdown->totalBalance,
            'is_paid' => $breakdown->totalBalance <= 0,
        ];
    }
}
