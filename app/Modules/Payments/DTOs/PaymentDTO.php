<?php

namespace App\Modules\Payments\DTOs;

class PaymentDTO
{
    public function __construct(
        public readonly int $production_order_id,
        public readonly float $amount,
        public readonly string $type,
        public readonly string $payment_method,
        public readonly ?string $notes,
        public readonly string $paid_at,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            production_order_id: $data['production_order_id'],
            amount: $data['amount'],
            type: $data['type'],
            payment_method: $data['payment_method'] ?? 'efectivo',
            notes: $data['notes'] ?? null,
            paid_at: $data['paid_at'] ?? now()->toDateString(),
        );
    }

    public function toArray(): array
    {
        return [
            'production_order_id' => $this->production_order_id,
            'amount' => $this->amount,
            'type' => $this->type,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'paid_at' => $this->paid_at,
        ];
    }
}
