<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Modules\Stages\Services\StageService;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function __construct(private StageService $service) {}

    public function index()
    {
        $stages = $this->service->list();

        return view('stages.index', compact('stages'));
    }

    public function create()
    {
        return view('stages.form', ['stage' => new Stage()]);
    }

    public function store(Request $request)
    {
        // NOTA: 'auto_complete' no se expone en este formulario a propósito
        // (etapa demasiado sensible para un checkbox suelto) — se gestiona
        // solo vía migración o directamente por la API. No se toca aquí,
        // así que las etapas nuevas creadas desde Web siempre quedan con el
        // default de la columna (false).
        $validated = $request->validate([
            'name'  => 'required|string|max:100',
            'order' => 'required|integer|min:1',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['active'] = true;

        $this->service->create($validated);

        return redirect('/stages')->with('success', 'Etapa creada.');
    }

    public function edit(Stage $stage)
    {
        return view('stages.form', compact('stage'));
    }

    public function update(Request $request, Stage $stage)
    {
        // NOTA: 'auto_complete' no se expone en este formulario (ver store()).
        // No se incluye en $validated para no pisar su valor actual con false
        // cada vez que se edite una etapa desde aquí.
        $validated = $request->validate([
            'name'   => 'required|string|max:100',
            'order'  => 'required|integer|min:1',
            'color'  => 'nullable|string|max:20',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        $this->service->update($stage, $validated);

        return redirect('/stages')->with('success', 'Etapa actualizada.');
    }

    public function destroy(Stage $stage)
    {
        try {
            $this->service->delete($stage);
        } catch (\Exception $e) {
            return redirect('/stages')->withErrors(['error' => $e->getMessage()]);
        }

        return redirect('/stages')->with('success', 'Etapa eliminada.');
    }

    public function show(Stage $stage)
    {
        return redirect('/stages');
    }
}
