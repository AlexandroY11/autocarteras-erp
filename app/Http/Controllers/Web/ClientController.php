<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Client;
use App\Models\Department;
use App\Modules\Clients\DTOs\ClientDTO;
use App\Modules\Clients\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function __construct(private ClientService $service) {}

    public function index()
    {
        $departments = Department::orderBy('name')->get();

        $clients = $this->service->paginate(20)->withQueryString();

        $total = Client::count();

        $byDepartment = Client::with('department')
            ->selectRaw('department_id, count(*) as total')
            ->whereNotNull('department_id')
            ->groupBy('department_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Ciudades del departamento seleccionado para el filtro
        $cities = request('department_id')
            ? City::where('department_id', request('department_id'))->orderBy('name')->get()
            : collect();

        return view('clients.index', compact(
            'clients', 'departments', 'total', 'byDepartment', 'cities'
        ));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('clients.form', ['client' => new Client(), 'departments' => $departments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:clients,phone',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'city_id' => 'required|exists:cities,id',
        ], [
            'phone.unique' => 'Este número de teléfono ya está registrado con otro cliente.',
            'department_id.required' => 'Debes seleccionar un departamento.',
            'city_id.required' => 'Debes seleccionar una ciudad.',
        ]);

        $validated['active'] = true;

        $this->service->create(ClientDTO::fromRequest($validated));

        return redirect('/clients')->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Client $client)
    {
        $departments = Department::orderBy('name')->get();

        return view('clients.form', compact('client', 'departments'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'max:20', Rule::unique('clients', 'phone')->ignore($client->id)],
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'city_id' => 'required|exists:cities,id',
        ], [
            'phone.unique' => 'Este número ya pertenece a otro cliente registrado.',
        ]);

        $validated['active'] = $request->boolean('active');

        $this->service->update($client, ClientDTO::fromRequest($validated));

        return redirect('/clients')->with('success', 'Cliente actualizado.');
    }

    public function destroy(Client $client)
    {
        $this->service->delete($client);

        return redirect('/clients')->with('success', 'Cliente eliminado.');
    }

    public function show(Client $client)
    {
        return redirect('/clients');
    }

    public function search(Request $request)
    {
        $clients = $this->service->search($request->get('q', ''));

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
