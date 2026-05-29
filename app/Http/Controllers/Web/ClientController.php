<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::query()
            ->when(request('search'), fn ($q, $s) => $q->where('first_name', 'ilike', "%{$s}%")
                  ->orWhere('last_name', 'ilike', "%{$s}%")
                  ->orWhere('phone', 'ilike', "%{$s}%")
            )
            ->orderBy('first_name')
            ->paginate(20);

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $departments = \App\Models\Department::orderBy('name')->get();

        return view('clients.form', ['client' => new Client(), 'departments' => $departments]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
        ]);

        Client::create($request->all());

        return redirect('/clients')->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Client $client)
    {
        $departments = \App\Models\Department::orderBy('name')->get();

        return view('clients.form', compact('client', 'departments'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
        ]);

        $client->update($request->all());

        return redirect('/clients')->with('success', 'Cliente actualizado correctamente.');
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
}
