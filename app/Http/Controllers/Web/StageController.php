<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function index()
    {
        $stages = Stage::orderBy('order')->get();

        return view('stages.index', compact('stages'));
    }

    public function create()
    {
        return view('stages.form', ['stage' => new Stage()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'order' => 'required|integer|min:1',
            'color' => 'nullable|string|max:20',
        ]);

        Stage::create([...$request->all(), 'active' => true]);

        return redirect('/stages')->with('success', 'Etapa creada.');
    }

    public function edit(Stage $stage)
    {
        return view('stages.form', compact('stage'));
    }

    public function update(Request $request, Stage $stage)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'order' => 'required|integer|min:1',
            'color' => 'nullable|string|max:20',
            'active' => 'boolean',
        ]);

        $stage->update([
            ...$request->all(),
            'active' => $request->boolean('active'),
        ]);

        return redirect('/stages')->with('success', 'Etapa actualizada.');
    }

    public function destroy(Stage $stage)
    {
        if ($stage->productionOrders()->exists()) {
            return redirect('/stages')->withErrors(['error' => 'No se puede eliminar una etapa con órdenes activas.']);
        }

        $stage->delete();

        return redirect('/stages')->with('success', 'Etapa eliminada.');
    }

    public function show(Stage $stage)
    {
        return redirect('/stages');
    }
}
