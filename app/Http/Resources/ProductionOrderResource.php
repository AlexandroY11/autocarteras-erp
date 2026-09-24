<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * price, shipping_price, payments y los saldos calculados nunca deben verse
 * fuera de Admin (docs/business-rules/01-roles-y-permisos.md: Director/Worker
 * "sin acceso financiero"). Antes ProductionOrderController devolvía el
 * modelo crudo — cualquier token autenticado (ability:*:read es decorativo,
 * ver hallazgo de Fase 2) veía precio y pagos completos de cualquier orden.
 */
class ProductionOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isAdmin = (bool) $request->user()?->isAdmin();

        return [
            'id' => $this->id,
            'consecutive' => $this->consecutive,
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client'),
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product'),
            'color' => $this->color,
            'sticker' => $this->sticker,
            'sticker_color' => $this->sticker_color,
            'observations' => $this->observations,
            'due_date' => $this->due_date,
            'current_stage_id' => $this->current_stage_id,
            'current_stage' => $this->whenLoaded('currentStage'),
            'status' => $this->status,
            'dispatch_status' => $this->dispatch_status,
            'created_by' => $this->created_by,
            'order_stages' => $this->whenLoaded('orderStages'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'price' => $this->when($isAdmin, $this->price),
            'shipping_price' => $this->when($isAdmin, $this->shipping_price),
            'payments' => $this->when(
                $isAdmin && $this->relationLoaded('payments'),
                fn () => PaymentResource::collection($this->payments)
            ),
            'total_paid' => $this->when($isAdmin, fn () => $this->total_paid),
            'shipping_paid' => $this->when($isAdmin, fn () => $this->shipping_paid),
            'shipping_balance' => $this->when($isAdmin, fn () => $this->shipping_balance),
            'product_paid' => $this->when($isAdmin, fn () => $this->product_paid),
            'product_balance' => $this->when($isAdmin, fn () => $this->product_balance),
            'total_balance' => $this->when($isAdmin, fn () => $this->total_balance),
        ];
    }
}
