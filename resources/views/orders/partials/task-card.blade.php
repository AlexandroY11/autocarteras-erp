@php
    $nextStageName = $order->nextStage()?->name ?? 'Finalizado';
    $confirmText = "¿Confirmas que terminaste {$order->product->name} en " . optional($order->currentStage)->name . "?";

    // Solo los campos que el cliente realmente tiene — nunca se rellena con
    // un placeholder tipo "Ciudad no registrada".
    $locationParts = array_filter([
        optional(optional($order->client)->city)->name,
        optional(optional($order->client)->department)->name,
    ]);
    $locationText = implode(', ', $locationParts);

    // Colores del badge de estado de despacho, cuando la producción ya
    // terminó (current_stage_id null) — mismo mapeo que orders/index.blade.php,
    // no hay un color centralizado en el modelo (solo DISPATCH_STATUS_LABELS
    // para el texto). En la práctica esto solo se ve cuando Admin mira
    // day-detail.blade.php: Worker/Director nunca llegan a este branch,
    // porque su query ya filtra por currentStage no nulo.
    $dispatchStatusColors = [
        'pending_dispatch' => 'bg-amber-500',
        'dispatched'       => 'bg-indigo-500',
        'sent'             => 'bg-blue-500',
        'delivered'        => 'bg-green-500',
        'returned'         => 'bg-red-500',
    ];
@endphp
<div
    data-swipeable
    data-can-advance="1"
    data-confirm-text="{{ $confirmText }}"
    class="relative overflow-hidden rounded-[2rem]"
>
    {{-- FONDO VERDE — muestra a qué etapa pasa, no un texto fijo --}}
    <div data-bg class="absolute inset-0 flex items-center gap-3 pl-8 rounded-[2rem] transition-colors duration-300"
        style="background: #3B6D11;">
        <svg class="w-7 h-7 text-green-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
        </svg>
        <span class="text-lg font-bold text-green-100">→ {{ $nextStageName }}</span>
    </div>

    {{-- FORM OCULTO --}}
    <form data-form method="POST" action="/production-orders/{{ $order->id }}/advance-stage" class="hidden">
        @csrf
    </form>

    {{-- TARJETA --}}
    <div data-card class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 space-y-4">
        <div class="flex items-start justify-between">
            <p class="text-3xl font-black text-gray-900 leading-none">
                #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}
            </p>
            <span class="px-3 py-1 text-xs font-bold rounded-full {{ $order->time_tracking_color_class }}">
                {{ $order->time_tracking_label ?? '—' }}
            </span>
        </div>

        <div>
            <h2 class="text-2xl font-black text-gray-900">
                {{ $order->product->name }}
            </h2>
            <p class="text-base text-gray-500">
                {{ optional($order->client)->full_name }}
            </p>
            @if($locationText)
                <p class="text-sm text-gray-400 flex items-center gap-1.5 mt-0.5">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.5-7.5 11.25-7.5 11.25S4.5 18 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                    {{ $locationText }}
                </p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3 text-base text-gray-700">
            <span class="inline-flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-2xl font-semibold">
                {{ $order->color }}
            </span>
            @if($order->sticker)
                <span class="inline-flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-2xl font-semibold">
                    Calcomanía {{ $order->sticker_color ?? '' }}
                </span>
            @endif
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Fecha compromiso</p>
                <p class="text-base font-bold text-gray-900">
                    {{ optional($order->due_date)->format('d/m/Y') }}
                    @if($order->business_days_remaining !== null)
                        <span class="text-sm font-medium text-gray-400">
                            ({{ $order->business_days_remaining >= 0 ? $order->business_days_remaining . ' días hábiles' : abs($order->business_days_remaining) . ' días hábiles de atraso' }})
                        </span>
                    @endif
                </p>
            </div>
            @if($order->currentStage)
                <span class="text-base font-bold text-white px-4 py-2 rounded-full shrink-0"
                      style="background: {{ $order->currentStage->color }}">
                    {{ $order->currentStage->name }}
                </span>
            @elseif($order->status === 'cancelled')
                {{-- Cancelada después de terminar producción (dispatch_status
                     sigue en pending_dispatch por la guarda de cancel()) — mostrar
                     "Terminado" ahí sería falso, así que se omite el badge de
                     despacho por completo. --}}
                <span class="text-base font-bold text-white px-4 py-2 rounded-full shrink-0 bg-red-500">
                    Cancelado
                </span>
            @else
                {{-- max-w + sin whitespace-nowrap: "Pendiente de despacho" no
                     cabe en una sola línea del ancho del pill — con shrink-0
                     solo (sin tope de ancho) se salía de la card entera en
                     vez de envolver. --}}
                <div class="flex flex-col items-end gap-1 shrink-0 max-w-[140px]">
                    <span class="text-sm font-bold text-white px-3 py-1 rounded-full bg-green-500">
                        Terminado
                    </span>
                    @if($order->dispatch_status)
                        <span class="text-sm font-bold text-white px-3 py-1 rounded-full text-center leading-tight {{ $dispatchStatusColors[$order->dispatch_status] ?? 'bg-gray-400' }}">
                            {{ $order->dispatch_status_label }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        {{-- PRECIO Y SALDO — solo Admin (sección 21), mismo criterio que
             orders/index.blade.php. Esta tarjeta la usan también Worker y
             Director (Mis tareas, día del calendario) — para ellos nunca
             se evalúa esto porque nunca son isAdmin(). --}}
        @if(auth()->user()->isAdmin())
            @php $balance = $order->product_balance; @endphp
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <span class="text-base font-medium text-gray-900">
                    ${{ number_format($order->price, 0, ',', '.') }}
                </span>
                @if($balance > 0)
                    <span class="text-xs text-red-600">
                        Debe ${{ number_format($balance, 0, ',', '.') }}
                    </span>
                @else
                    <span class="text-xs text-green-700 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Pagado
                    </span>
                @endif
            </div>
        @endif
    </div>
</div>
