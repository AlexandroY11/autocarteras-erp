<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.form', ['supplier' => new Supplier]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        Supplier::create([...$request->only('name', 'phone'), 'active' => true]);

        return redirect('/suppliers')->with('success', 'Proveedor creado correctamente.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $supplier->update([
            ...$request->only('name', 'phone'),
            'active' => $request->boolean('active', true),
        ]);

        return redirect('/suppliers')->with('success', 'Proveedor actualizado.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return redirect('/suppliers')->withErrors(['error' => 'No se puede eliminar un proveedor con compras registradas.']);
        }
        $supplier->delete();
        return redirect('/suppliers')->with('success', 'Proveedor eliminado.');
    }

    public function show(Supplier $supplier)
    {
        return redirect('/suppliers');
    }
}