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

    public function create(PaymentDTO $dto, int $userId): Payment
    {
        $order = ProductionOrder::findOrFail($dto->production_order_id);

        // Validar que no se pague más de lo que falta
        $totalPaid = $order->payments()->sum('amount');
        $balance = $order->price - $totalPaid;

        if ($dto->amount > $balance) {
            throw new \Exception("El pago ({$dto->amount}) supera el saldo pendiente ({$balance}).", 422);
        }

        $payment = Payment::create([
            ...$dto->toArray(),
            'registered_by' => $userId,
        ]);

        // Si el saldo queda en 0, marcar orden como entregada
        $newBalance = $balance - $dto->amount;
        if ($newBalance <= 0 && $order->status === 'done') {
            $order->update(['status' => 'delivered']);
        }

        return $payment->load('registeredBy');
    }

    public function delete(Payment $payment): void
    {
        $payment->delete();
    }

    public function summary(ProductionOrder $order): array
    {
        $payments = $order->payments()->sum('amount');
        $balance = $order->price - $payments;

        return [
            'price' => $order->price,
            'total_paid' => $payments,
            'balance' => $balance,
            'is_paid' => $balance <= 0,
        ];
    }
}
