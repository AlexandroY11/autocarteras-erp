<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Client;
use App\Models\Department;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('name')->get();

        $clients = Client::with(['department', 'city'])
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
            ->paginate(20)
            ->withQueryString();

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
        return view('clients.form', ['client' => new Client, 'departments' => $departments]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'phone'         => 'required|string|max:20',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'city_id'       => 'nullable|exists:cities,id',
        ]);

        Client::create($request->only(
            'first_name', 'last_name', 'phone', 'email',
            'address', 'department_id', 'city_id'
        ) + ['active' => true]);

        return redirect('/clients')->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Client $client)
    {
        $departments = Department::orderBy('name')->get();
        return view('clients.form', compact('client', 'departments'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'phone'         => 'required|string|max:20',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'city_id'       => 'nullable|exists:cities,id',
        ]);

        $client->update($request->only(
            'first_name', 'last_name', 'phone', 'email',
            'address', 'department_id', 'city_id', 'active'
        ));

        return redirect('/clients')->with('success', 'Cliente actualizado.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect('/clients')->with('success', 'Cliente eliminado.');
    }

    public function show(Client $client)
    {
        return redirect('/clients');
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $clients = Client::query()
            ->where('active', true)
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'ilike', "%{$q}%")
                    ->orWhere('last_name', 'ilike', "%{$q}%")
                    ->orWhere('phone', 'ilike', "%{$q}%");
            })
            ->with('city')
            ->with('department')
            ->limit(6)
            ->get();

        return response()->json(
            $clients->map(fn ($c) => [
                'id'         => $c->id,
                'name'       => $c->full_name,
                'phone'      => $c->phone,
                'address'    => $c->address,
                'city'       => $c->city?->name ?? '',
                'department' => $c->department?->name ?? '',
            ])
        );
    }
}