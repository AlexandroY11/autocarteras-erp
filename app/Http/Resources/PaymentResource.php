<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'production_order_id' => $this->production_order_id,
            'amount' => $this->amount,
            'type' => $this->type,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'paid_at' => $this->paid_at,
            'registered_by' => $this->whenLoaded('registeredBy'),
            'created_at' => $this->created_at,
        ];
    }
}
