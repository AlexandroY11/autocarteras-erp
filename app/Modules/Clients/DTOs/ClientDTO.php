<?php

namespace App\Modules\Clients\DTOs;

class ClientDTO
{
    public function __construct(
        public readonly string  $first_name,
        public readonly string  $last_name,
        public readonly string  $phone,
        public readonly ?string $email,
        public readonly ?string $address,
        public readonly ?int    $department_id,
        public readonly ?int    $city_id,
        public readonly bool    $active,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            first_name:    $data['first_name'],
            last_name:     $data['last_name'],
            phone:         $data['phone'],
            email:         $data['email'] ?? null,
            address:       $data['address'] ?? null,
            department_id: $data['department_id'] ?? null,
            city_id:       $data['city_id'] ?? null,
            active:        $data['active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'first_name'    => $this->first_name,
            'last_name'     => $this->last_name,
            'phone'         => $this->phone,
            'email'         => $this->email,
            'address'       => $this->address,
            'department_id' => $this->department_id,
            'city_id'       => $this->city_id,
            'active'        => $this->active,
        ];
    }
}
