<?php

namespace App\Modules\Stages\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Modules\Stages\Services\StageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    public function __construct(private StageService $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->list());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'order'         => 'required|integer|min:1',
            'color'         => 'nullable|string|max:20',
            'active'        => 'boolean',
            'auto_complete' => 'boolean',
        ]);

        $stage = $this->service->create($validated);

        return response()->json($stage, 201);
    }

    public function show(Stage $stage): JsonResponse
    {
        return response()->json($stage);
    }

    public function update(Request $request, Stage $stage): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:100',
            'order'         => 'sometimes|required|integer|min:1',
            'color'         => 'nullable|string|max:20',
            'active'        => 'sometimes|boolean',
            'auto_complete' => 'sometimes|boolean',
        ]);

        $stage = $this->service->update($stage, $validated);

        return response()->json($stage);
    }

    public function destroy(Stage $stage): JsonResponse
    {
        try {
            $this->service->delete($stage);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        }

        return response()->json(null, 204);
    }
}
