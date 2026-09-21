<?php

namespace App\Modules\Dispatch\Services;

use App\Models\OrderDispatch;
use App\Models\Payment;
use App\Models\ProductionOrder;
use App\Models\User;

class DispatchService
{
    /**
     * Genera una guía nueva. Exige que la orden esté lista para despachar
     * (pending_dispatch) y que el envío ya esté cubierto (shipping_balance <= 0).
     * El recaudo de la guía se deriva del saldo de producto en este momento,
     * no lo escribe el usuario.
     *
     * El número de guía es opcional aquí a propósito: cambiar de estado y
     * capturar la guía son dos pasos independientes (ver setGuideNumber()).
     */
    public function dispatch(ProductionOrder $order, ?string $guideNumber, User $user): OrderDispatch
    {
        if ($order->dispatch_status !== 'pending_dispatch') {
            throw new \Exception('La orden no está lista para despachar.', 422);
        }

        if ($order->shipping_balance > 0) {
            throw new \Exception(
                "No se puede despachar: falta cubrir el envío (saldo de envío: {$order->shipping_balance}).",
                422
            );
        }

        $collectedAmount = $order->product_balance > 0 ? $order->product_balance : null;

        $dispatch = $order->orderDispatches()->create([
            'guide_number'     => $guideNumber,
            'collected_amount' => $collectedAmount,
            'dispatched_at'    => now(),
            'dispatched_by'    => $user->id,
        ]);

        $order->update(['dispatch_status' => 'dispatched']);

        return $dispatch;
    }

    /**
     * Asigna o corrige el número de guía de la orden, en cualquier momento
     * después de despachada — independiente del dispatch_status actual
     * (funciona igual en dispatched, sent, delivered o returned). Opera
     * sobre el despacho más reciente, no solo sobre el "abierto"
     * (currentDispatch() excluye delivered/returned, y aquí sí se necesita
     * poder corregir la guía después de entregado).
     */
    public function setGuideNumber(ProductionOrder $order, string $guideNumber): OrderDispatch
    {
        $dispatch = $order->orderDispatches()->latest('dispatched_at')->first();

        if (! $dispatch) {
            throw new \Exception('Esta orden todavía no tiene ningún despacho registrado.', 422);
        }

        $dispatch->update(['guide_number' => $guideNumber]);

        return $dispatch;
    }

    public function markSent(ProductionOrder $order, User $user): OrderDispatch
    {
        if ($order->dispatch_status !== 'dispatched') {
            throw new \Exception('La orden no está en estado despachado.', 422);
        }

        $dispatch = $order->currentDispatch();

        if (! $dispatch) {
            throw new \Exception('No se encontró la guía abierta para esta orden.', 422);
        }

        $dispatch->update([
            'sent_at' => now(),
            'sent_by' => $user->id,
        ]);

        $order->update(['dispatch_status' => 'sent']);

        return $dispatch;
    }

    /**
     * Marca la orden como entregada. Si queda saldo de producto pendiente,
     * genera automáticamente un Payment tipo 'cod' por ese monto exacto,
     * de forma idempotente (uno por guía, nunca duplicado).
     */
    public function markDelivered(ProductionOrder $order, User $user): OrderDispatch
    {
        if ($order->dispatch_status !== 'sent') {
            throw new \Exception('La orden no está en tránsito.', 422);
        }

        $dispatch = $order->currentDispatch();

        if (! $dispatch) {
            throw new \Exception('No se encontró la guía abierta para esta orden.', 422);
        }

        $productBalance = $order->product_balance;

        $dispatch->update([
            'delivered_at' => now(),
            'delivered_by' => $user->id,
        ]);

        $order->update(['dispatch_status' => 'delivered']);

        if ($productBalance > 0) {
            $alreadyCollected = Payment::where('order_dispatch_id', $dispatch->id)
                ->where('type', 'cod')
                ->exists();

            if (! $alreadyCollected) {
                Payment::create([
                    'production_order_id' => $order->id,
                    'order_dispatch_id'   => $dispatch->id,
                    'amount'              => $productBalance,
                    'type'                => 'cod',
                    'payment_method'      => 'efectivo',
                    'notes'               => 'Recaudo automático contraentrega',
                    'paid_at'             => now(),
                    'registered_by'       => $user->id,
                ]);
            }
        }

        return $dispatch;
    }

    public function markReturned(ProductionOrder $order, string $reason, User $user): OrderDispatch
    {
        if (! in_array($order->dispatch_status, ['dispatched', 'sent'], true)) {
            throw new \Exception('La orden no está en tránsito ni despachada.', 422);
        }

        $dispatch = $order->currentDispatch();

        if (! $dispatch) {
            throw new \Exception('No se encontró la guía abierta para esta orden.', 422);
        }

        $dispatch->update([
            'returned_at'   => now(),
            'returned_by'   => $user->id,
            'return_reason' => $reason,
        ]);

        $order->update(['dispatch_status' => 'returned']);

        return $dispatch;
    }

    /**
     * Paso explícito: una orden devuelta debe pasar por aquí antes de poder
     * generar una guía nueva — nunca se salta directo a "dispatched".
     */
    public function returnToPendingDispatch(ProductionOrder $order): ProductionOrder
    {
        if ($order->dispatch_status !== 'returned') {
            throw new \Exception('La orden no está en estado devuelto.', 422);
        }

        $order->update(['dispatch_status' => 'pending_dispatch']);

        return $order;
    }
}
