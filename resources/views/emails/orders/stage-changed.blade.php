@extends('emails.layouts.layout')

@section('subject', $subject)

@section('badge', 'Actualización de Producción')

@section('content')

    <p class="greeting">¡Hola, {{ $order->client->first_name }}!</p>
    <p class="intro">
        Tu orden ha avanzado a una nueva etapa de producción. Aquí te contamos cómo va tu pedido.
    </p>

    {{-- ETAPA ACTUAL --}}
    <div class="highlight-box">
        Tu orden <strong>#{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}</strong>
        ha pasado a la etapa:
        <strong style="font-size:16px; display:block; margin-top:6px; color:#c45f00;">
            {{ $stage->name }}
        </strong>
    </div>

    {{-- CARD DE ORDEN --}}
    <div class="order-card">
        <div class="order-card-header">
            <span class="order-number">ORDEN #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}</span>
            <span class="order-status" style="background: {{ $stage->color }}">{{ $stage->name }}</span>
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
            @if($order->due_date)
            <div class="order-row">
                <span class="label">Fecha estimada de entrega</span>
                <span class="value">{{ $order->due_date->format('d/m/Y') }}</span>
            </div>
            @endif
            <div class="order-row">
                <span class="label">Saldo pendiente</span>
                <span class="value">${{ number_format($order->balance, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

@endsection
