<?php

namespace App\Services\Mail;

use App\Mail\OrderCreatedMail;
use App\Mail\OrderPaymentMail;
use App\Mail\OrderShippedMail;
use App\Mail\OrderStageChangedMail;
use App\Models\OrderDispatch;
use App\Models\Payment;
use App\Models\ProductionOrder;
use App\Models\Stage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Psr\Log\LoggerInterface;

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

    /**
     * Copia oculta de archivo, pegada solo al envío al cliente (no llega si
     * el cliente no tiene correo registrado).
     */
    private const BCC_ARCHIVE = 'autocarterascali@gmail.com';

    /**
     * Canal de log dedicado a diagnosticar envíos de correo, separado del
     * laravel.log general — un archivo de texto por día en storage/logs/mail/.
     *
     * Se construye "on demand" con Log::build() en vez de declararlo en
     * config/logging.php a propósito: si algún día se corre
     * `php artisan config:cache`, un canal declarado ahí con la fecha de
     * HOY calculada una sola vez quedaría congelado en esa fecha para
     * siempre. Log::build() evalúa la ruta en cada llamada, así que
     * siempre escribe en el archivo del día real, con o sin config cacheado.
     */
    private static function mailLog(): LoggerInterface
    {
        return Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/mail/'.now()->format('Y-m-d').'.txt'),
            'level' => 'debug',
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // ORDEN CREADA
    // ─────────────────────────────────────────────────────────

    public static function orderCreated(ProductionOrder $order): bool
    {
        // Claim atómico: si esta orden ya fue notificada, no se reenvía.
        // Se marca ANTES de enviar (cero duplicados), a costa de no
        // reintentar si el envío falla por una razón transitoria.
        $claimed = ProductionOrder::where('id', $order->id)
            ->whereNull('created_notified_at')
            ->update(['created_notified_at' => now()]);

        if (! $claimed) {
            self::mailLog()->info("orderCreated: OMITIDO — orden #{$order->consecutive} (id={$order->id}) ya estaba marcada como notificada (created_notified_at ya tenía valor).");
            return false;
        }

        $mailable = new OrderCreatedMail($order);
        $sent = self::sendToClient($order, $mailable, "orderCreated (orden #{$order->consecutive}, id={$order->id})");
        self::sendToAdmins($mailable, "orderCreated (orden #{$order->consecutive}, id={$order->id})");
        return $sent;
    }

    // ─────────────────────────────────────────────────────────
    // CAMBIO DE ETAPA
    // ─────────────────────────────────────────────────────────
    //
    // Pausado a propósito (decisión del producto, MVP — evitar saturar de
    // correos a un cliente que recién empieza a operar con la app). El
    // método, el Mailable y la vista quedan intactos para reactivarlo
    // cuando se decida. Ver el call-site comentado en
    // ProductionOrderController::advanceStage().

    public static function orderStageChanged(ProductionOrder $order, Stage $stage): bool
    {
        $mailable = new OrderStageChangedMail($order, $stage);
        $sent = self::sendToClient($order, $mailable, "orderStageChanged (orden #{$order->consecutive}, id={$order->id}, etapa={$stage->name})");
        self::sendToAdmins($mailable, "orderStageChanged (orden #{$order->consecutive}, id={$order->id}, etapa={$stage->name})");
        return $sent;
    }

    // ─────────────────────────────────────────────────────────
    // ORDEN DESPACHADA
    // ─────────────────────────────────────────────────────────

    public static function orderShipped(ProductionOrder $order, OrderDispatch $dispatch): bool
    {
        $claimed = OrderDispatch::where('id', $dispatch->id)
            ->whereNull('shipped_notified_at')
            ->update(['shipped_notified_at' => now()]);

        if (! $claimed) {
            self::mailLog()->info("orderShipped: OMITIDO — orden #{$order->consecutive} (id={$order->id}), despacho id={$dispatch->id} ya estaba marcado como notificado (shipped_notified_at ya tenía valor).");
            return false;
        }

        $mailable = new OrderShippedMail($order, $dispatch);
        $sent = self::sendToClient($order, $mailable, "orderShipped (orden #{$order->consecutive}, id={$order->id}, dispatch id={$dispatch->id})");
        self::sendToAdmins($mailable, "orderShipped (orden #{$order->consecutive}, id={$order->id}, dispatch id={$dispatch->id})");
        return $sent;
    }

    // ─────────────────────────────────────────────────────────
    // PAGO REGISTRADO
    // ─────────────────────────────────────────────────────────

    public static function orderPaymentRegistered(ProductionOrder $order, Payment $payment): bool
    {
        $claimed = Payment::where('id', $payment->id)
            ->whereNull('notified_at')
            ->update(['notified_at' => now()]);

        if (! $claimed) {
            self::mailLog()->info("orderPaymentRegistered: OMITIDO — orden #{$order->consecutive} (id={$order->id}), pago id={$payment->id} ya estaba marcado como notificado (notified_at ya tenía valor).");
            return false;
        }

        $mailable = new OrderPaymentMail($order, $payment);
        $sent = self::sendToClient($order, $mailable, "orderPaymentRegistered (orden #{$order->consecutive}, id={$order->id}, pago id={$payment->id})");
        self::sendToAdmins($mailable, "orderPaymentRegistered (orden #{$order->consecutive}, id={$order->id}, pago id={$payment->id})");
        return $sent;
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────────────

    /**
     * Envía al cliente solo si tiene email registrado.
     */
    private static function sendToClient(ProductionOrder $order, $mailable, string $context): bool
    {
        $email = $order->client->email ?? null;

        if (! $email) {
            self::mailLog()->info("{$context}: SIN EMAIL — el cliente de la orden #{$order->consecutive} no tiene correo registrado, no se envía nada al cliente.");
            return false;
        }

        self::mailLog()->info("{$context}: intentando enviar a cliente [{$email}] (BCC: ".self::BCC_ARCHIVE.").");

        try {
            Mail::to($email)->bcc(self::BCC_ARCHIVE)->send(clone $mailable);
            self::mailLog()->info("{$context}: OK — envío exitoso a cliente [{$email}].");
            return true;
        } catch (\Throwable $e) {
            self::mailLog()->error("{$context}: FALLÓ el envío a cliente [{$email}] — ".get_class($e).': '.$e->getMessage());
            Log::error("MailService: error enviando al cliente [{$email}] — {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Envía copia a todos los administradores.
     */
    private static function sendToAdmins($mailable, string $context): void
    {
        foreach (self::$admins as $admin) {
            try {
                Mail::to($admin)->send(clone $mailable);
                self::mailLog()->info("{$context}: OK — envío exitoso a admin [{$admin}].");
            } catch (\Throwable $e) {
                self::mailLog()->error("{$context}: FALLÓ el envío a admin [{$admin}] — ".get_class($e).': '.$e->getMessage());
                Log::error("MailService: error enviando a admin [{$admin}] — {$e->getMessage()}");
            }
        }
    }
}
