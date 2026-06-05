<?php

namespace App\Services\Mail;

use App\Mail\OrderCreatedMail;
use App\Mail\OrderPaymentMail;
use App\Mail\OrderShippedMail;
use App\Mail\OrderStageChangedMail;
use App\Models\Payment;
use App\Models\ProductionOrder;
use App\Models\Stage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Emails de administradores que siempre reciben copia de todo.
     */
    private static array $admins = [
        'admin@autocarterascali.com',
        'jhon.yule@autocarterascali.com',
        'alexandro.yule@autocarterascali.com',
    ];

    // ─────────────────────────────────────────────────────────
    // ORDEN CREADA
    // ─────────────────────────────────────────────────────────

    public static function orderCreated(ProductionOrder $order): bool
    {
        $mailable = new OrderCreatedMail($order);
        $sent = self::sendToClient($order, $mailable);
        self::sendToAdmins($mailable);
        return $sent;
    }

    // ─────────────────────────────────────────────────────────
    // CAMBIO DE ETAPA
    // ─────────────────────────────────────────────────────────

    public static function orderStageChanged(ProductionOrder $order, Stage $stage): bool
    {
        $mailable = new OrderStageChangedMail($order, $stage);
        $sent = self::sendToClient($order, $mailable);
        self::sendToAdmins($mailable);
        return $sent;
    }

    // ─────────────────────────────────────────────────────────
    // ORDEN DESPACHADA
    // ─────────────────────────────────────────────────────────

    public static function orderShipped(ProductionOrder $order): bool
    {
        $mailable = new OrderShippedMail($order);
        $sent = self::sendToClient($order, $mailable);
        self::sendToAdmins($mailable);
        return $sent;
    }

    // ─────────────────────────────────────────────────────────
    // PAGO REGISTRADO
    // ─────────────────────────────────────────────────────────

    public static function orderPaymentRegistered(ProductionOrder $order, Payment $payment): bool
    {
        $mailable = new OrderPaymentMail($order, $payment);
        $sent = self::sendToClient($order, $mailable);
        self::sendToAdmins($mailable);
        return $sent;
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────

    /**
     * Envía al cliente solo si tiene email registrado.
     */
    private static function sendToClient(ProductionOrder $order, $mailable): bool
    {
        $email = $order->client->email ?? null;

        if (! $email) {
            Log::info("MailService: cliente sin email — orden #{$order->consecutive}");
            return false;
        }

        try {
            Mail::to($email)->send(clone $mailable);
            return true;
        } catch (\Throwable $e) {
            Log::error("MailService: error enviando al cliente [{$email}] — {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Envía copia a todos los administradores.
     */
    private static function sendToAdmins($mailable): void
    {
        foreach (self::$admins as $admin) {
            try {
                Mail::to($admin)
                    ->send(clone $mailable);
            } catch (\Throwable $e) {
                Log::error("MailService: error enviando a admin [{$admin}] — {$e->getMessage()}");
            }
        }
    }
}