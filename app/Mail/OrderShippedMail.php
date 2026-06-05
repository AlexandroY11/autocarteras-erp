<?php

namespace App\Mail;

use App\Models\ProductionOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProductionOrder $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "¡Tu orden #{$this->order->consecutive} fue despachada! — AutoCarteras Cali",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.shipped',
            with: [
                'order'   => $this->order->load(['client.city', 'client.department', 'product', 'payments']),
                'subject' => '¡Tu pedido va en camino!',
            ],
        );
    }
}