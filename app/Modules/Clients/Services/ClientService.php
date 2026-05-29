<?php

namespace App\Modules\Clients\Services;

use App\Models\Client;
use App\Modules\Clients\DTOs\ClientDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientService
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return Client::query()
            ->when(request('search'), fn($q, $s) =>
                $q->where('first_name', 'ilike', "%{$s}%")
                  ->orWhere('last_name',  'ilike', "%{$s}%")
                  ->orWhere('phone',      'ilike', "%{$s}%")
            )
            ->when(request('active') !== null, fn($q) =>
                $q->where('active', filter_var(request('active'), FILTER_VALIDATE_BOOLEAN))
            )
            ->orderBy('first_name')
            ->paginate($perPage);
    }

    public function create(ClientDTO $dto): Client
    {
        return Client::create($dto->toArray());
    }

    public function update(Client $client, ClientDTO $dto): Client
    {
        $client->update($dto->toArray());
        return $client->fresh();
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }
}