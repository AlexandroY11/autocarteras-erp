<?php

namespace App\Modules\Production\DTOs;

class ProductionOrderDTO
{
    public function __construct(
        public readonly int     $client_id,
        public readonly int     $product_id,
        public readonly string  $color,
        public readonly bool    $sticker,
        public readonly ?string $sticker_color,
        public readonly ?string $observations,
        public readonly float   $price,
        public readonly string  $due_date,
        // Opcionales, default = comportamiento actual — ningún caller
        // existente (web/API del ERP) los pasa, así que no cambian nada
        // para ellos. Solo los usa WhatsappOrderController.
        public readonly ?string $idempotency_key = null,
        public readonly string  $origin = 'manual',
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            client_id:       $data['client_id'],
            product_id:      $data['product_id'],
            color:           $data['color'],
            sticker:         $data['sticker'] ?? false,
            sticker_color:   $data['sticker_color'] ?? null,
            observations:    $data['observations'] ?? null,
            price:           $data['price'],
            due_date:        $data['due_date'],
            idempotency_key: $data['idempotency_key'] ?? null,
            origin:          $data['origin'] ?? 'manual',
        );
    }

    public function toArray(): array
    {
        return [
            'client_id'       => $this->client_id,
            'product_id'      => $this->product_id,
            'color'           => $this->color,
            'sticker'         => $this->sticker,
            'sticker_color'   => $this->sticker_color,
            'observations'    => $this->observations,
            'price'           => $this->price,
            'due_date'        => $this->due_date,
            'idempotency_key' => $this->idempotency_key,
            'origin'          => $this->origin,
        ];
    }
}
