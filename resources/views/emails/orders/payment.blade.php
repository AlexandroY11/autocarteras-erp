@extends('emails.layouts.layout')

@section('subject', $subject)

@section('badge', 'Pago Recibido')

@section('content')

    <p class="greeting">¡Hola, {{ $order->client->first_name }}!</p>
    <p class="intro">
        Hemos registrado un pago para tu orden. A continuación el resumen actualizado.
    </p>

    {{-- CARD DE ORDEN --}}
    <div class="order-card">
        <div class="order-card-header">
            <span class="order-number">ORDEN #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}</span>
            @if($order->balance <= 0)
                <span class="order-status" style="background:#16a34a;">Pagado</span>
            @else
                <span class="order-status">Pago parcial</span>
            @endif
        </div>
        <div class="order-card-body">
            <div class="order-row">
                <span class="label">Producto</span>
                <span class="value">{{ $order->product->name }}</span>
            </div>
            <div class="order-row">
                <span class="label">Pago recibido</span>
                <span class="value" style="color:#16a34a;">
                    +${{ number_format($payment->amount, 0, ',', '.') }}
                </span>
            </div>
            <div class="order-row">
                <span class="label">Total pagado</span>
                <span class="value">${{ number_format($order->total_paid, 0, ',', '.') }}</span>
            </div>
            <div class="order-row">
                <span class="label">Valor total</span>
                <span class="value">${{ number_format($order->price, 0, ',', '.') }}</span>
            </div>
            <div class="order-row">
                <span class="label">Saldo pendiente</span>
                <span class="value" style="color: {{ $order->balance > 0 ? '#dc2626' : '#16a34a' }}">
                    ${{ number_format($order->balance, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    @if($order->balance <= 0)
    <div class="highlight-box">
        <strong>¡Tu orden está completamente pagada!</strong>
        Gracias por tu confianza en AutoCarteras Cali.
    </div>
    @else
    <div class="highlight-box">
        Aún tienes un saldo pendiente de <strong>${{ number_format($order->balance, 0, ',', '.') }}</strong>.
        Este valor se cobrará al momento de la entrega.
    </div>
    @endif

@endsection
