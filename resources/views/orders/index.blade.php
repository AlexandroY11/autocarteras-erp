<x-app-layout title="Órdenes">
<div class="pt-4">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Órdenes</h1>
            <p class="text-sm text-gray-500">{{ $orders->total() }} órdenes activas</p>
        </div>
        <a href="/production-orders/create"
            class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm active:scale-95 transition-transform">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva
        </a>
    </div>

    {{-- Búsqueda --}}
    <form method="GET" action="/orders" class="mb-3">
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Buscar cliente o # orden..."
                class="input-field pl-12 text-base">
        </div>
    </form>

    {{-- Filtros por etapa --}}
    <div class="flex gap-2 overflow-x-auto pb-2 mb-4 scrollbar-hide">
        <a href="/orders"
            class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition-all
            {{ !request('stage') ? 'bg-blue-700 text-white' : 'bg-white text-gray-600 border border-gray-200' }}">
            Todas
        </a>
        @foreach($stages as $stage)
        <a href="/orders?stage={{ $stage->id }}"
            class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold transition-all border
            {{ request('stage') == $stage->id ? 'text-white border-transparent' : 'bg-white text-gray-600 border-gray-200' }}"
            style="{{ request('stage') == $stage->id ? 'background:'.$stage->color.';border-color:'.$stage->color : '' }}">
            {{ $stage->name }}
        </a>
        @endforeach
    </div>

    {{-- Lista de órdenes --}}
    <div class="space-y-3">
        @forelse($orders as $order)
        <a href="/production-orders/{{ $order->id }}"
            class="block bg-white card p-4 active:scale-99 transition-transform">
            <div class="flex justify-between items-start gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold text-gray-400">#{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}</span>
                        @if($order->currentStage)
                        <span class="status-badge text-white"
                            style="background: {{ $order->currentStage->color }}">
                            {{ $order->currentStage->name }}
                        </span>
                        @else
                        <span class="status-badge
                            {{ $order->status === 'done' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $order->status === 'delivered' ? 'bg-gray-100 text-gray-600' : '' }}">
                            {{ $order->status === 'done' ? '✓ Terminada' : 'Entregada' }}
                        </span>
                        @endif
                    </div>
                    <div class="text-lg font-bold text-gray-900 truncate">{{ $order->client->full_name }}</div>
                    <div class="text-sm text-gray-500 truncate">{{ $order->product->name }}</div>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="flex items-center gap-1 text-sm font-medium text-gray-700">
                            <span class="w-3 h-3 rounded-full border border-gray-300 inline-block"
                                style="background: {{ strtolower($order->color) === 'negro' ? '#111' : (strtolower($order->color) === 'gris' ? '#9ca3af' : '#f5f0e8') }}">
                            </span>
                            {{ $order->color }}
                        </span>
                        @if($order->sticker)
                        <span class="text-xs bg-yellow-50 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded-full font-medium">
                            🏷 Calcomanía {{ $order->sticker_color }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-base font-bold text-gray-900">${{ number_format($order->price, 0, ',', '.') }}</div>
                    @php $balance = $order->price - $order->payments->sum('amount') @endphp
                    @if($balance > 0)
                    <div class="text-xs text-red-600 font-semibold">Debe ${{ number_format($balance, 0, ',', '.') }}</div>
                    @else
                    <div class="text-xs text-green-600 font-semibold">✓ Pagado</div>
                    @endif
                    <div class="text-xs text-gray-400 mt-1">
                        {{ $order->due_date->format('d/m') }}
                        @if($order->due_date->isPast() && !in_array($order->status, ['done','delivered']))
                        <span class="text-red-500 font-bold"> ⚠</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="text-center py-16">
            <div class="text-6xl mb-4">📋</div>
            <div class="text-xl font-bold text-gray-400">Sin órdenes activas</div>
            <div class="text-sm text-gray-400 mt-1">Crea una nueva orden para comenzar</div>
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>
</x-app-layout>