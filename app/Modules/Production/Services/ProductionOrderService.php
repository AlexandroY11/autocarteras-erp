<?php

namespace App\Modules\Production\Services;

use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Stage;
use App\Models\User;
use App\Modules\Production\DTOs\ProductionOrderDTO;
use App\Services\Mail\MailService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

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

    /**
     * Notifica al cliente que su orden fue creada — único punto de envío
     * para los dos canales (web y bot de WhatsApp), que antes solo existía
     * en el controller web.
     *
     * No vive dentro de create() a propósito: la plantilla del correo
     * muestra total_paid/total_balance, que solo son correctos después de
     * registrar el anticipo (que ambos canales crean después de create()).
     * Debe llamarse además fuera de la transacción: un correo enviado
     * dentro de una que luego revierte le avisaría al cliente de un pedido
     * que dejó de existir.
     *
     * Ojo con qué significa "enviar" acá: OrderCreatedMail implementa
     * ShouldQueue, así que el ->send() de MailService en realidad **encola**
     * — Mailer::sendMailable() deriva a ->queue() cuando el Mailable es
     * ShouldQueue (vendor/laravel/framework/src/Illuminate/Mail/Mailer.php:349-354).
     * La entrega real la hace el worker después, fuera de este request.
     *
     * Nunca propaga una excepción: para cuando esto corre, el pedido ya
     * está comiteado, así que un problema con el correo no puede
     * convertirse en un error de la respuesta. MailService ya captura lo que
     * falle al encolar, pero no el claim de created_notified_at (escritura
     * a BD) ni Log::build() (escribe en storage/logs/mail/).
     */
    public function notifyCreated(ProductionOrder $order): bool
    {
        try {
            return MailService::orderCreated($order);
        } catch (\Throwable $e) {
            Log::error("notifyCreated: falló la notificación de la orden #{$order->consecutive} (id={$order->id}) — ".get_class($e).': '.$e->getMessage());

            return false;
        }
    }

    /**
     * Campos que se fijan al crear y que ningún camino de edición debe
     * reescribir. No es una precaución teórica: ninguno de los dos
     * controllers de edición (web y API) los incluye al armar el DTO, así
     * que ProductionOrderDTO::toArray() aporta sus valores por defecto
     * (null / 'manual') y un update() a secas los escribía encima,
     * silenciosamente:
     *
     * - seller_id    -> NULL: se perdía quién vendió.
     * - origin       -> 'manual': un pedido del bot pasaba a figurar como
     *                   creado a mano.
     * - idempotency_key -> NULL: lo más grave. Sin la key, un reintento de
     *                   n8n con esa misma key ya no encuentra la orden y
     *                   crea un PEDIDO DUPLICADO.
     *
     * La protección vive acá y no en los controllers para que valga para
     * los dos caminos y para cualquiera que se agregue después.
     */
    private const CREATION_ONLY_FIELDS = ['seller_id', 'origin', 'idempotency_key'];

    public function update(ProductionOrder $order, ProductionOrderDTO $dto): ProductionOrder
    {
        $order->update(Arr::except($dto->toArray(), self::CREATION_ONLY_FIELDS));

        return $order->load(['client', 'product', 'currentStage']);
    }

    public function advanceStage(ProductionOrder $order, User $user, ?string $notes = null): ProductionOrder
    {
        // Antes vivía duplicado (idéntico) en el controller web y en el de
        // API — cualquiera que agregara un tercer consumidor, o editara uno
        // sin replicar el otro, perdía esta protección en silencio.
        if (in_array($order->status, ['done', 'cancelled'], true)) {
            throw new \Exception('Esta orden no puede avanzar de etapa.', 422);
        }

        // "Enviado" es una etapa inactiva hoy — este guard solo protege
        // pedidos legacy que quedaron con current_stage_id apuntando ahí
        // antes de la desactivación. Solo el controller web lo tenía.
        if ($order->current_stage_id !== null && $order->current_stage_id === Stage::enviadoId()) {
            throw new \Exception('La orden ya se encuentra en la etapa Enviado.', 422);
        }

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
        // Antes vivía duplicado (idéntico) en el controller web y en el de
        // API — este método no validaba nada por sí solo.
        if (! in_array($order->dispatch_status, [null, 'pending_dispatch'], true)) {
            throw new \Exception('No se puede cancelar una orden que ya fue despachada.', 422);
        }

        $order->update(['status' => 'cancelled']);
        return $order;
    }
}