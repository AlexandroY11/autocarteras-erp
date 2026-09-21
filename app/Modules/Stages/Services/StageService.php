<?php

namespace App\Modules\Stages\Services;

use App\Models\Stage;
use Illuminate\Database\Eloquent\Collection;

class StageService
{
    public function list(): Collection
    {
        return Stage::orderBy('order')->get();
    }

    public function create(array $data): Stage
    {
        return Stage::create($data);
    }

    public function update(Stage $stage, array $data): Stage
    {
        $stage->update($data);
        return $stage->fresh();
    }

    public function delete(Stage $stage): void
    {
        if ($stage->productionOrders()->exists()) {
            throw new \Exception('No se puede eliminar una etapa con órdenes activas.', 422);
        }

        $stage->delete();
    }
}
