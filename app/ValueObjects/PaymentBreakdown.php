<?php

namespace App\ValueObjects;

readonly class PaymentBreakdown
{
    public function __construct(
        public float $shippingPrice,
        public float $productPrice,
        public float $shippingPaid,
        public float $shippingBalance,
        public float $productPaid,
        public float $productBalance,
        public float $totalPaid,
        public float $totalOwed,
        public float $totalBalance,
    ) {}
}
