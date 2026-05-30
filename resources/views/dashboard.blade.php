<x-app-layout title="Dashboard">

    {{-- Estadísticas --}}
    <div class="grid grid-cols-2 gap-3 mt-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
            <div class="text-xs text-yellow-700 mt-1">Pendientes</div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <div class="text-3xl font-bold text-blue-600">{{ $stats['in_progress'] }}</div>
            <div class="text-xs text-blue-700 mt-1">En producción</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <div class="text-3xl font-bold text-green-600">{{ $stats['done'] }}</div>
            <div class="text-xs text-green-700 mt-1">Terminadas</div>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
            <div class="text-3xl font-bold text-gray-600">{{ $stats['delivered'] }}</div>
            <div class="text-xs text-gray-700 mt-1">Entregadas</div>
        </div>
    </div>

    {{-- Búsqueda --}}
    <form method="GET" action="/dashboard" class="mt-4">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Buscar por cliente o # orden..."
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </form>

    {{-- Botón nueva orden --}}
    <a href="/production-orders/create"
        class="mt-4 flex items-center justify-center gap-2 bg-blue-700 text-white font-semibold py-3 rounded-xl w-full">
        + Nueva Orden
    </a>

    {{-- Filtro por etapa --}}
    <div class="mt-4 flex gap-2 overflow-x-auto pb-1">
        <a href="/dashboard"
            class="shrink-0 text-xs px-3 py-1 rounded-full border {{ !request('stage') ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-gray-600 border-gray-300' }}">
            Todas
        </a>
        @foreach($stages as $stage)
        <a href="/dashboard?stage={{ $stage->id }}"
            class="shrink-0 text-xs px-3 py-1 rounded-full border {{ request('stage') == $stage->id ? 'text-white border-transparent' : 'bg-white text-gray-600 border-gray-300' }}"
            style="{{ request('stage') == $stage->id ? 'background-color:'.$stage->color.';border-color:'.$stage->color : '' }}">
            {{ $stage->name }}
        </a>
        @endforeach
    </div>

    {{-- Lista de órdenes --}}
    <div class="mt-3 space-y-3">
        @forelse($orders as $order)
        <a href="/production-orders/{{ $order->id }}"
            class="block bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs text-gray-400">#{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}</span>
                    <div class="font-semibold text-gray-800 mt-0.5">{{ $order->client->full_name }}</div>
                    <div class="text-sm text-gray-500">{{ $order->product->name }}</div>
                    <div class="text-sm text-gray-500">Color: <span class="font-medium">{{ $order->color }}</span></div>
                </div>
                <div class="text-right">
                    @if($order->currentStage)
                    <span class="text-xs text-white px-2 py-1 rounded-full"
                        style="background-color: {{ $order->currentStage->color }}">
                        {{ $order->currentStage->name }}
                    </span>
                    @endif
                    <div class="text-xs text-gray-400 mt-2">
                        {{ \Carbon\Carbon::parse($order->due_date)->format('d/m/Y') }}
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="text-center text-gray-400 py-12">
            <div class="text-4xl mb-2">📋</div>
            <div>No hay órdenes activas</div>
        </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $orders->links() }}
    </div>

</x-app-layout>