<?php

namespace App\Mail;

use App\Models\ProductionOrder;
use App\Models\Stage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStageChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProductionOrder $order,
        public Stage $stage
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu orden #{$this->order->consecutive} avanzó a: {$this->stage->name} — AutoCarteras Cali",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.stage-changed',
            with: [
                'order'   => $this->order->load(['client', 'product', 'payments']),
                'stage'   => $this->stage,
                'subject' => "Etapa actualizada: {$this->stage->name}",
            ],
        );
    }
}