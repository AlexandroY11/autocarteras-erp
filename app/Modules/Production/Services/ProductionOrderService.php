<?php

namespace App\Modules\Production\Services;

use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Models\User;
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

        // Snapshot del precio de envío del catálogo — fijo desde la creación,
        // no cambia aunque el producto se actualice después.
        $shippingPrice = Product::find($dto->product_id)?->shipping_price;

        $order = ProductionOrder::create([
            ...$dto->toArray(),
            'consecutive'      => $consecutive,
            'current_stage_id' => $firstStage?->id,
            'status'           => 'pending',
            'created_by'       => $userId,
            'shipping_price'   => $shippingPrice,
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

    public function advanceStage(ProductionOrder $order, User $user, ?string $notes = null): ProductionOrder
    {
        if (! $order->current_stage_id || ! $user->canAdvanceStage($order->current_stage_id)) {
            throw new \Exception('No tienes la habilidad asignada para avanzar esta etapa.', 403);
        }

        // Completar etapa actual
        $currentOrderStage = $order->orderStages()
            ->whereNull('completed_at')
            ->latest()
            ->first();

        if ($currentOrderStage) {
            $currentOrderStage->update([
                'completed_at' => now(),
                'assigned_to'  => $user->id,
                'notes'        => $notes,
            ]);
        }

        // Buscar siguiente etapa
        $nextStage = $order->nextStage();

        if (!$nextStage) {
            // No hay más etapas — marcar como done y lista para despacho
            $order->update([
                'status'           => 'done',
                'current_stage_id' => null,
                'dispatch_status'  => 'pending_dispatch',
            ]);
        } else {
            $order->update([
                'current_stage_id' => $nextStage->id,
                'status'           => 'in_progress',
            ]);

            $newOrderStage = $order->orderStages()->create([
                'stage_id'   => $nextStage->id,
                'started_at' => now(),
            ]);

            if ($nextStage->auto_complete) {
                // Etapas como "Finalizado" no las trabaja nadie — se completan
                // solas apenas se llega a ellas y la orden pasa directo a done.
                $newOrderStage->update([
                    'completed_at' => now(),
                    'assigned_to'  => $user->id,
                ]);

                $order->update([
                    'status'           => 'done',
                    'current_stage_id' => null,
                    'dispatch_status'  => 'pending_dispatch',
                ]);
            }
        }

        return $order->load(['client', 'product', 'currentStage', 'orderStages.stage']);
    }

    public function cancel(ProductionOrder $order): ProductionOrder
    {
        $order->update(['status' => 'cancelled']);
        return $order;
    }
}