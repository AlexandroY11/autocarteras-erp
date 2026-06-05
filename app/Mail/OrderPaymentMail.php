<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\ProductionOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProductionOrder $order,
        public Payment $payment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pago registrado en tu orden #{$this->order->consecutive} — AutoCarteras Cali",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.payment',
            with: [
                'order'   => $this->order->load(['client', 'product', 'payments']),
                'payment' => $this->payment,
                'subject' => 'Pago recibido',
            ],
        );
    }
}