<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Supplier;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::with('supplier')->orderBy('name')->get();
        return view('materials.index', compact('materials'));
    }

    public function create()
    {
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        return view('materials.form', ['material' => new Material, 'suppliers' => $suppliers]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'unit'        => 'required|in:kg,g,lt,ml,unidad',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        Material::create([...$request->only('name', 'unit', 'supplier_id'), 'active' => true]);

        return redirect('/materials')->with('success', 'Material creado correctamente.');
    }

    public function edit(Material $material)
    {
        $suppliers = Supplier::where('active', true)->orderBy('name')->get();
        return view('materials.form', compact('material', 'suppliers'));
    }

    public function update(Request $request, Material $material)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'unit'        => 'required|in:kg,g,lt,ml,unidad',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $material->update([
            ...$request->only('name', 'unit', 'supplier_id'),
            'active' => $request->boolean('active', true),
        ]);

        return redirect('/materials')->with('success', 'Material actualizado.');
    }

    public function destroy(Material $material)
    {
        if ($material->purchases()->exists()) {
            return redirect('/materials')->withErrors(['error' => 'No se puede eliminar un material con compras registradas.']);
        }
        $material->delete();
        return redirect('/materials')->with('success', 'Material eliminado.');
    }

    public function show(Material $material)
    {
        return redirect('/materials');
    }
}