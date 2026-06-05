@extends('emails.layouts.layout')

@section('subject', $subject)

@section('badge', '¡Tu Pedido Va en Camino!')

@section('content')

    <p class="greeting">¡Hola, {{ $order->client->first_name }}!</p>
    <p class="intro">
        Excelentes noticias: tu cartera ya fue despachada y está en camino hacia ti.
        Por favor ten a mano el saldo pendiente para el momento de la entrega.
    </p>

    {{-- CARD DE ORDEN --}}
    <div class="order-card">
        <div class="order-card-header">
            <span class="order-number">ORDEN #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}</span>
            <span class="order-status">Despachada</span>
        </div>
        <div class="order-card-body">
            <div class="order-row">
                <span class="label">Producto</span>
                <span class="value">{{ $order->product->name }}</span>
            </div>
            <div class="order-row">
                <span class="label">Color</span>
                <span class="value">{{ $order->color }}</span>
            </div>
            @if($order->sticker)
            <div class="order-row">
                <span class="label">Calcomanía</span>
                <span class="value">{{ $order->sticker_color ?? 'Sí' }}</span>
            </div>
            @endif
            <div class="order-row">
                <span class="label">Dirección de entrega</span>
                <span class="value">
                    {{ $order->client->address }},
                    {{ $order->client->city->name }},
                    {{ $order->client->department->name }}
                </span>
            </div>
            <div class="order-row">
                <span class="label">Valor total</span>
                <span class="value">${{ number_format($order->price, 0, ',', '.') }}</span>
            </div>
            <div class="order-row">
                <span class="label">Total pagado</span>
                <span class="value">${{ number_format($order->total_paid, 0, ',', '.') }}</span>
            </div>
            <div class="order-row">
                <span class="label">Saldo a pagar en entrega</span>
                <span class="value" style="color: {{ $order->balance > 0 ? '#dc2626' : '#16a34a' }}">
                    ${{ number_format($order->balance, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    @if($order->balance > 0)
    <div class="highlight-box">
        Recuerda tener listo el pago de <strong>${{ number_format($order->balance, 0, ',', '.') }}</strong>
        al momento de recibir tu pedido.
    </div>
    @endif

@endsection
