<?php

namespace App\Modules\Clients\Services;

use App\Models\Client;
use App\Modules\Clients\DTOs\ClientDTO;
use Illuminate\Database\Eloquent\Collection;
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
            ->when(request('department_id'), fn($q, $d) =>
                $q->where('department_id', $d)
            )
            ->when(request('city_id'), fn($q, $c) =>
                $q->where('city_id', $c)
            )
            ->when(request('active') !== null, fn($q) =>
                $q->where('active', filter_var(request('active'), FILTER_VALIDATE_BOOLEAN))
            )
            ->orderBy('first_name')
            ->paginate($perPage);
    }

    /**
     * Búsqueda para adjuntar un cliente a una operación activa (ej. crear un
     * pedido). Excluye siempre inactivos — no es el listado administrativo
     * general, es específicamente para "encontrar un cliente utilizable".
     */
    public function search(string $q, int $limit = 6): Collection
    {
        if (strlen($q) < 2) {
            return new Collection();
        }

        return Client::query()
            ->where('active', true)
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'ilike', "%{$q}%")
                    ->orWhere('last_name', 'ilike', "%{$q}%")
                    ->orWhere('phone', 'ilike', "%{$q}%");
            })
            ->with(['city', 'department'])
            ->limit($limit)
            ->get();
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
