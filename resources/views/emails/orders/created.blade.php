@extends('emails.layouts.layout')

@section('subject', $subject)

@section('badge', 'Nueva Orden')

@section('content')

    <p class="greeting">¡Hola, {{ $order->client->first_name }}!</p>
    <p class="intro">
        Hemos recibido tu orden de producción con éxito. A continuación encontrarás el resumen de tu pedido.
        Nuestro equipo comenzará a trabajar en él muy pronto.
    </p>

    {{-- CARD DE ORDEN --}}
    <div class="order-card">
        <div class="order-card-header">
            <span class="order-number">ORDEN #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}</span>
            <span class="order-status">Recibida</span>
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
            @if($order->due_date)
            <div class="order-row">
                <span class="label">Fecha estimada de entrega</span>
                <span class="value">{{ $order->due_date->format('d/m/Y') }}</span>
            </div>
            @endif
            <div class="order-row">
                <span class="label">Valor total</span>
                <span class="value">${{ number_format($order->price, 0, ',', '.') }}</span>
            </div>
            <div class="order-row">
                <span class="label">Anticipo pagado</span>
                <span class="value">${{ number_format($order->advance_payment, 0, ',', '.') }}</span>
            </div>
            <div class="order-row">
                <span class="label">Saldo pendiente</span>
                <span class="value">${{ number_format($order->balance, 0, ',', '.') }}</span>
            </div>
            @if($order->observations)
            <div class="order-row">
                <span class="label">Observaciones</span>
                <span class="value">{{ $order->observations }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="highlight-box">
        Te notificaremos por correo cada vez que tu orden avance a una nueva etapa de producción.
        Si tienes preguntas, puedes contactarnos directamente por WhatsApp.
    </div>

@endsection