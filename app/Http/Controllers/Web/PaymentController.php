<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ProductionOrder;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'production_order_id' => 'required|exists:production_orders,id',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:advance,partial,final',
            'payment_method' => 'required|in:efectivo,nequi',
            'notes' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        $order = ProductionOrder::findOrFail($request->production_order_id);

        $totalPaid = $order->payments()->sum('amount');
        $balance = $order->price - $totalPaid;

        if ($request->amount > $balance) {
            return back()->withErrors(['error' => "El pago supera el saldo pendiente ($balance)."]);
        }

        Payment::create([
            'production_order_id' => $order->id,
            'amount' => $request->amount,
            'type' => $request->type,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'paid_at' => $request->paid_at ?? now()->toDateString(),
            'registered_by' => auth()->id(),
        ]);

        // Si saldo queda en 0 y orden está done, marcar como entregada
        $newBalance = $balance - $request->amount;
        if ($newBalance <= 0 && $order->status === 'done') {
            $order->update(['status' => 'delivered']);
        }

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return back()->with('success', 'Pago eliminado.');
    }
}
