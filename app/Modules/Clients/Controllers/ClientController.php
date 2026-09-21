<?php

namespace App\Modules\Clients\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\Clients\DTOs\ClientDTO;
use App\Modules\Clients\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function __construct(private ClientService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'phone'         => 'required|string|max:20|unique:clients,phone',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'city_id'       => 'required|exists:cities,id',
            'active'        => 'boolean',
        ]);

        $client = $this->service->create(ClientDTO::fromRequest($validated));

        return response()->json($client, 201);
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json($client);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'first_name'    => 'sometimes|required|string|max:100',
            'last_name'     => 'sometimes|required|string|max:100',
            'phone'         => ['sometimes', 'required', 'string', 'max:20', Rule::unique('clients', 'phone')->ignore($client->id)],
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string|max:255',
            'department_id' => 'sometimes|required|exists:departments,id',
            'city_id'       => 'sometimes|required|exists:cities,id',
            'active'        => 'sometimes|boolean',
        ]);

        $client = $this->service->update($client, ClientDTO::fromRequest([
            ...$client->only(['first_name', 'last_name', 'phone', 'email', 'address', 'department_id', 'city_id', 'active']),
            ...$validated,
        ]));

        return response()->json($client);
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->service->delete($client);
        return response()->json(null, 204);
    }

    public function search(): JsonResponse
    {
        $clients = $this->service->search(request('q', ''));

        return response()->json(
            $clients->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->full_name,
                'phone' => $c->phone,
                'address' => $c->address,
                'city' => $c->city?->name ?? '',
                'department' => $c->department?->name ?? '',
            ])
        );
    }
}
