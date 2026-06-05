<?php

namespace App\Mail;

use App\Models\ProductionOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProductionOrder $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nueva orden #" . str_pad($this->order->consecutive, 3, '0', STR_PAD_LEFT) . " — AutoCarteras Cali",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.created',
            with: [
                'order'   => $this->order->load(['client.city', 'client.department', 'product', 'payments']),
                'subject' => "Nueva orden #" . str_pad($this->order->consecutive, 3, '0', STR_PAD_LEFT),
            ],
        );
    }
}