<x-app-layout title="Órdenes del día">
    <div class="pt-4 pb-20 space-y-5 bg-gray-50/50 min-h-screen">
        
        {{-- HEADER --}}
        <div class="px-4">
            <a href="/production-orders/calendar" class="text-blue-600 flex items-center gap-1 text-sm font-bold mb-3 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Volver al Calendario
            </a>
            <div class="flex items-end justify-between">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 capitalize leading-tight">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d \d\e F') }}
                    </h1>
                    <p class="text-sm font-medium text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded-lg mt-1">
                        {{ $orders->count() }} órdenes programadas
                    </p>
                </div>
            </div>
        </div>

        {{-- LISTADO DE ÓRDENES (REPLICANDO TU ESTILO DE INDEX) --}}
        <div class="px-4 space-y-3">
            @forelse($orders as $order)
                <div class="relative overflow-hidden rounded-2xl bg-white border border-gray-100 shadow-sm">
                    <a href="/production-orders/{{ $order->id }}" class="block active:scale-[0.99] transition-transform">
                        
                        {{-- FILA SUPERIOR (Número, Producto, Cliente, Etapa) --}}
                        <div class="flex items-stretch border-b border-gray-100">
                            {{-- NÚMERO DE ORDEN --}}
                            <div class="flex flex-col items-center justify-center px-5 py-4 bg-gray-50 border-r border-gray-100 min-w-[88px]">
                                <span class="text-[11px] text-gray-400 uppercase tracking-widest">Ord.</span>
                                <span class="text-2xl font-medium text-gray-900 leading-tight">
                                    #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>

                            {{-- PRODUCTO Y CLIENTE --}}
                            <div class="flex flex-col justify-center px-5 py-4 flex-1 gap-0.5 min-w-0">
                                <p class="text-lg font-medium text-gray-900 truncate">{{ $order->product->name }}</p>
                                <p class="text-sm text-gray-400 flex items-center gap-1.5 truncate">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                    </svg>
                                    {{ $order->client->full_name }}
                                </p>
                            </div>

                            {{-- ETAPA --}}
                            <div class="flex items-center gap-2 px-3 py-4 border-l border-gray-100 shrink-0 max-w-[120px]">
                                @if($order->currentStage)
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $order->currentStage->color }}"></span>
                                    <span class="text-sm font-medium truncate" style="color: {{ $order->currentStage->color }}">
                                        {{ $order->currentStage->name }}
                                    </span>
                                @else
                                    <span class="w-2.5 h-2.5 rounded-full bg-gray-300 shrink-0"></span>
                                    <span class="text-sm font-medium text-gray-400">Sin etapa</span>
                                @endif
                            </div>
                        </div>

                        {{-- FILA INFERIOR (Metadatos - Replicando el scroll horizontal de móvil) --}}
                        <div class="flex items-center overflow-x-auto no-scrollbar border-t border-gray-100 bg-white">
                            {{-- COLOR --}}
                            <div class="flex items-center gap-2 px-[18px] py-3 text-sm text-gray-600 border-r border-gray-100 shrink-0">
                                <span class="w-3 h-3 rounded-full border border-gray-300 shrink-0" style="background: {{ $order->color }}"></span>
                                {{ $order->color }}
                            </div>

                            {{-- CALCOMANÍAS --}}
                            @if($order->sticker_color)
                                <div class="flex items-center gap-2 px-[18px] py-3 text-sm font-medium text-amber-700 bg-amber-50 border-r border-amber-200 shrink-0">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L9.568 3z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                                    </svg>
                                    {{ $order->sticker_color }}
                                </div>
                            @endif

                            {{-- PIEZAS --}}
                            <div class="flex items-center gap-2 px-[18px] py-3 text-sm text-gray-600 border-r border-gray-100 shrink-0">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                                </svg>
                                {{ $order->product->pieces ?? 0 }} pz
                            </div>

                            {{-- PRECIO / SALDO (Al final a la derecha) --}}
                            <div class="ml-auto px-[18px] py-3 flex flex-col items-end shrink-0">
                                @php $balance = $order->price - $order->payments->sum('amount'); @endphp
                                <span class="text-base font-medium text-gray-900">${{ number_format($order->price, 0, ',', '.') }}</span>
                                @if($balance > 0)
                                    <span class="text-[10px] font-bold text-red-600 uppercase">Debe ${{ number_format($balance, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-[10px] font-bold text-green-700 uppercase">Pagado</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="text-center py-20 bg-white rounded-[2.5rem] border border-dashed border-gray-200">
                    <h3 class="text-xl font-bold text-gray-400">Sin órdenes</h3>
                    <p class="text-sm text-gray-400 mt-1">No hay entregas programadas para este día</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
