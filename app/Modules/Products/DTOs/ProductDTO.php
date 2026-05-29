<?php

namespace App\Modules\Products\DTOs;

class ProductDTO
{
    public function __construct(
        public readonly string  $name,
        public readonly ?string $description,
        public readonly int     $pieces,
        public readonly int     $avg_production_days,
        public readonly float   $base_price,
        public readonly ?string $photo,
        public readonly bool    $active,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name:                 $data['name'],
            description:          $data['description'] ?? null,
            pieces:               $data['pieces'] ?? 1,
            avg_production_days:  $data['avg_production_days'] ?? 7,
            base_price:           $data['base_price'],
            photo:                $data['photo'] ?? null,
            active:               $data['active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'name'                => $this->name,
            'description'         => $this->description,
            'pieces'              => $this->pieces,
            'avg_production_days' => $this->avg_production_days,
            'base_price'          => $this->base_price,
            'photo'               => $this->photo,
            'active'              => $this->active,
        ];
    }
}