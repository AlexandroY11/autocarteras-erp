<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Payments\DTOs\PaymentDTO;
use App\Modules\Payments\Services\PaymentService;
use App\Services\Mail\MailService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $service) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'production_order_id' => 'required|exists:production_orders,id',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:advance,partial,final',
            'payment_method' => 'required|in:efectivo,nequi,nu',
            'notes' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        try {
            $payment = $this->service->create(
                PaymentDTO::fromRequest($validated),
                auth()->id()
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // El correo de confirmación es responsabilidad de este controlador
        // (canal Web), no del PaymentService compartido — ver la nota en
        // PaymentService::create() sobre por qué no vive ahí todavía.
        $order = $payment->productionOrder->load(['client', 'product', 'payments']);
        $emailSent = MailService::orderPaymentRegistered($order, $payment);

        $msg = 'Pago registrado correctamente.';
        $msg .= $emailSent ? ' Cliente notificado por correo.' : ' El cliente no tiene correo registrado.';

        return back()->with('success', $msg);
    }
}
