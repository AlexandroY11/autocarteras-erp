<x-app-layout title="Bolsillo de envíos">
    <div class="pt-4 space-y-5">

        {{-- HEADER --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-end">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Bolsillo de envíos</h1>
                <p class="text-sm font-medium text-amber-700 bg-amber-50 inline-block px-2 py-0.5 rounded-lg mt-1">
                    {{ $orders->count() }} {{ $orders->count() === 1 ? 'pedido despachado' : 'pedidos despachados' }} con el envío sin pagar
                </p>
            </div>

            <a href="/orders"
               class="flex items-center gap-2 bg-gray-800 text-white font-bold px-4 py-2.5 rounded-2xl text-sm shadow-md shadow-gray-200 active:scale-95 transition-all self-start">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                Volver a órdenes
            </a>
        </div>

        {{-- TOTAL --}}
        @if($orders->isNotEmpty())
            <div class="bg-amber-50 border border-amber-200 rounded-[2rem] p-5">
                <p class="text-[11px] font-black uppercase tracking-[0.15em] text-amber-700">Total de envíos por cobrar</p>
                <p class="text-3xl font-black text-amber-900 mt-1">
                    ${{ number_format($orders->sum(fn ($order) => $order->shipping_balance), 0, ',', '.') }}
                </p>
            </div>
        @endif

        {{-- LISTA --}}
        <div class="space-y-3">
            @forelse($orders as $order)
                <a href="/production-orders/{{ $order->id }}"
                   class="block bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-gray-400">
                                Orden #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}
                            </p>
                            <p class="text-base font-bold text-gray-900 truncate mt-0.5">
                                {{ $order->client->full_name }}
                            </p>
                            @if($order->dispatch_status_label)
                                <span class="inline-block text-[10px] font-black uppercase mt-2 px-2 py-0.5 rounded-full
                                    {{ $order->dispatch_status === 'returned'
                                        ? 'bg-red-50 text-red-700'
                                        : 'bg-gray-100 text-gray-500' }}">
                                    {{ $order->dispatch_status_label }}
                                </span>
                            @endif
                        </div>

                        <div class="text-right shrink-0">
                            <p class="text-[11px] font-black uppercase tracking-[0.15em] text-amber-600">Envío pendiente</p>
                            <p class="text-xl font-black text-amber-700 mt-0.5">
                                ${{ number_format($order->shipping_balance, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-gray-50 border border-gray-200 rounded-[2rem] p-8 text-center">
                    <p class="text-sm font-semibold text-gray-500">
                        No hay envíos pendientes de cobro.
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Un pedido entra acá cuando se despacha con el envío sin pagar, y sale solo en cuanto se registra el pago.
                    </p>
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>
