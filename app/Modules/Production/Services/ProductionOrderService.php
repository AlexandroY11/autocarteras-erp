<?php

namespace App\Modules\Production\Services;

use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Modules\Production\DTOs\ProductionOrderDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductionOrderService
{
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return ProductionOrder::query()
            ->with(['client', 'product', 'currentStage', 'createdBy'])
            ->when(request('search'), fn($q, $s) =>
                $q->whereHas('client', fn($q) =>
                    $q->where('first_name', 'ilike', "%{$s}%")
                      ->orWhere('last_name',  'ilike', "%{$s}%")
                )
                ->orWhere('consecutive', 'like', "%{$s}%")
            )
            ->when(request('status'), fn($q, $s) =>
                $q->where('status', $s)
            )
            ->when(request('stage_id'), fn($q, $s) =>
                $q->where('current_stage_id', $s)
            )
            ->orderByDesc('consecutive')
            ->paginate($perPage);
    }

    public function create(ProductionOrderDTO $dto, int $userId): ProductionOrder
    {
        // Consecutivo automático
        $consecutive = (ProductionOrder::withTrashed()->max('consecutive') ?? 0) + 1;

        // Primera etapa configurada
        $firstStage = Stage::where('active', true)
                           ->orderBy('order')
                           ->first();

        $order = ProductionOrder::create([
            ...$dto->toArray(),
            'consecutive'      => $consecutive,
            'current_stage_id' => $firstStage?->id,
            'status'           => 'pending',
            'created_by'       => $userId,
        ]);

        // Registrar la etapa inicial en trazabilidad
        if ($firstStage) {
            $order->orderStages()->create([
                'stage_id'   => $firstStage->id,
                'started_at' => now(),
            ]);
        }

        return $order->load(['client', 'product', 'currentStage']);
    }

    public function update(ProductionOrder $order, ProductionOrderDTO $dto): ProductionOrder
    {
        $order->update($dto->toArray());
        return $order->load(['client', 'product', 'currentStage']);
    }

    public function advanceStage(ProductionOrder $order, int $userId, ?string $notes = null): ProductionOrder
    {
        // Completar etapa actual
        $currentOrderStage = $order->orderStages()
            ->whereNull('completed_at')
            ->latest()
            ->first();

        if ($currentOrderStage) {
            $currentOrderStage->update([
                'completed_at' => now(),
                'assigned_to'  => $userId,
                'notes'        => $notes,
            ]);
        }

        // Buscar siguiente etapa
        $nextStage = Stage::where('active', true)
            ->where('order', '>', optional($order->currentStage)->order ?? 0)
            ->orderBy('order')
            ->first();

        if (!$nextStage) {
            // No hay más etapas — marcar como done
            $order->update([
                'status'           => 'done',
                'current_stage_id' => null,
            ]);
        } else {
            $order->update([
                'current_stage_id' => $nextStage->id,
                'status'           => 'in_progress',
            ]);

            $order->orderStages()->create([
                'stage_id'   => $nextStage->id,
                'started_at' => now(),
            ]);
        }

        return $order->load(['client', 'product', 'currentStage', 'orderStages.stage']);
    }

    public function cancel(ProductionOrder $order): ProductionOrder
    {
        $order->update(['status' => 'cancelled']);
        return $order;
    }

    public function delete(ProductionOrder $order): void
    {
        $order->delete();
    }
}