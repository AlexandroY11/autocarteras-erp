<?php

namespace App\Modules\Stages\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Stage::orderBy('order')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:100',
            'order'  => 'required|integer|min:1',
            'color'  => 'nullable|string|max:20',
            'active' => 'boolean',
        ]);

        $stage = Stage::create($validated);

        return response()->json($stage, 201);
    }

    public function show(Stage $stage): JsonResponse
    {
        return response()->json($stage);
    }

    public function update(Request $request, Stage $stage): JsonResponse
    {
        $validated = $request->validate([
            'name'   => 'sometimes|required|string|max:100',
            'order'  => 'sometimes|required|integer|min:1',
            'color'  => 'nullable|string|max:20',
            'active' => 'boolean',
        ]);

        $stage->update($validated);

        return response()->json($stage);
    }

    public function destroy(Stage $stage): JsonResponse
    {
        // No permitir eliminar si hay órdenes en esta etapa
        if ($stage->productionOrders()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar una etapa con órdenes activas.',
            ], 422);
        }

        $stage->delete();

        return response()->json(null, 204);
    }
}