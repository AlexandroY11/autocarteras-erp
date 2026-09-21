<?php

namespace App\Services;

use App\Models\ProductionOrder;
use App\ValueObjects\PaymentBreakdown;

class PaymentAllocationService
{
    /**
     * Distribuye los pagos de una orden: primero cubren el envío (shipping_price),
     * el excedente se aplica al producto (price). Se recalcula completo cada vez
     * a partir de los pagos existentes — no hay estado de asignación guardado.
     */
    public function breakdown(ProductionOrder $order): PaymentBreakdown
    {
        $shippingPrice = (float) ($order->shipping_price ?? 0);
        $productPrice  = (float) $order->price;

        $shippingRemaining = $shippingPrice;
        $productRemaining  = $productPrice;
        $shippingPaid = 0.0;
        $productPaid  = 0.0;

        $payments = $order->payments()
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get(['amount']);

        foreach ($payments as $payment) {
            $amount = (float) $payment->amount;

            $toShipping = min($amount, $shippingRemaining);
            $shippingPaid += $toShipping;
            $shippingRemaining -= $toShipping;
            $amount -= $toShipping;

            $toProduct = min($amount, $productRemaining);
            $productPaid += $toProduct;
            $productRemaining -= $toProduct;
        }

        $totalPaid = $shippingPaid + $productPaid;
        $totalOwed = $shippingPrice + $productPrice;

        return new PaymentBreakdown(
            shippingPrice: $shippingPrice,
            productPrice: $productPrice,
            shippingPaid: $shippingPaid,
            shippingBalance: $shippingRemaining,
            productPaid: $productPaid,
            productBalance: $productRemaining,
            totalPaid: $totalPaid,
            totalOwed: $totalOwed,
            totalBalance: $totalOwed - $totalPaid,
        );
    }
}
