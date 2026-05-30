<x-app-layout title="Dashboard">
<div class="pt-4 space-y-5">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-black text-gray-900">Panel</h1>
        <p class="text-sm text-gray-500">{{ now()->isoFormat('dddd, D [de] MMMM') }}</p>
    </div>

    {{-- Alerta vencidas --}}
    @if($stats['overdue'] > 0)
    <a href="/orders?status=overdue"
        class="flex items-center gap-3 bg-red-50 border-2 border-red-200 rounded-2xl p-4">
        <div class="text-3xl">⚠️</div>
        <div>
            <div class="font-bold text-red-700 text-base">{{ $stats['overdue'] }} orden(es) vencida(s)</div>
            <div class="text-sm text-red-500">Requieren atención inmediata</div>
        </div>
        <svg class="w-5 h-5 text-red-400 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
    @endif

    {{-- Métricas del mes --}}
    <div>
        <p class="section-title mb-3">Este mes</p>
        <div class="grid grid-cols-2 gap-3">
            <div class="card bg-white p-4">
                <div class="text-xs font-semibold text-gray-400 mb-1">Recaudado</div>
                <div class="text-2xl font-black text-green-600">${{ number_format($monthlyRevenue, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">Pagos recibidos</div>
            </div>
            <div class="card bg-white p-4">
                <div class="text-xs font-semibold text-gray-400 mb-1">Órdenes</div>
                <div class="text-2xl font-black text-blue-700">{{ $monthlyOrders }}</div>
                <div class="text-xs text-gray-400 mt-1">Nuevas este mes</div>
            </div>
            <div class="card bg-white p-4">
                <div class="text-xs font-semibold text-gray-400 mb-1">Por cobrar</div>
                <div class="text-2xl font-black text-orange-500">${{ number_format($totalPending, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">Saldo pendiente</div>
            </div>
            <div class="card bg-white p-4">
                <div class="text-xs font-semibold text-gray-400 mb-1">Entregadas</div>
                <div class="text-2xl font-black text-gray-700">{{ $stats['delivered'] }}</div>
                <div class="text-xs text-gray-400 mt-1">Total histórico</div>
            </div>
        </div>
    </div>

    {{-- Estado de producción --}}
    <div>
        <p class="section-title mb-3">Estado de producción</p>
        <div class="card bg-white p-4 space-y-3">
            @foreach($byStage as $stage)
            @if($stage->orders_count > 0)
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full shrink-0" style="background: {{ $stage->color }}"></div>
                <div class="flex-1">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-semibold text-gray-700">{{ $stage->name }}</span>
                        <span class="text-sm font-bold text-gray-900">{{ $stage->orders_count }}</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        @php $max = $byStage->max('orders_count'); @endphp
                        <div class="h-full rounded-full transition-all"
                            style="width: {{ $max > 0 ? ($stage->orders_count / $max * 100) : 0 }}%; background: {{ $stage->color }}">
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
            @if($byStage->sum('orders_count') === 0)
            <div class="text-center text-gray-400 py-4 text-sm">Sin órdenes en producción</div>
            @endif
        </div>
    </div>

    {{-- Órdenes vencidas --}}
    @if($overdueOrders->count() > 0)
    <div>
        <p class="section-title mb-3">Órdenes vencidas</p>
        <div class="space-y-2">
            @foreach($overdueOrders as $order)
            <a href="/production-orders/{{ $order->id }}"
                class="flex items-center gap-3 bg-white card p-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
                    <span class="text-red-600 font-bold text-sm">#{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-800 truncate">{{ $order->client->full_name }}</div>
                    <div class="text-xs text-gray-500 truncate">{{ $order->product->name }}</div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-xs font-bold text-red-600">
                        {{ $order->due_date->diffForHumans() }}
                    </div>
                    @if($order->currentStage)
                    <div class="text-xs text-white px-2 py-0.5 rounded-full mt-1 inline-block"
                        style="background: {{ $order->currentStage->color }}">
                        {{ $order->currentStage->name }}
                    </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Top ciudades --}}
    @if($topCities->count() > 0)
    <div>
        <p class="section-title mb-3">Top ciudades</p>
        <div class="card bg-white p-4 space-y-3">
            @foreach($topCities as $city => $total)
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-sm">
                    📍
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-semibold text-gray-700">{{ $city ?: 'Sin ciudad' }}</span>
                        <span class="text-sm font-bold text-gray-900">{{ $total }} órdenes</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full"
                            style="width: {{ $topCities->max() > 0 ? ($total / $topCities->max() * 100) : 0 }}%">
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Acceso rápido --}}
    <div>
        <p class="section-title mb-3">Acciones rápidas</p>
        <div class="grid grid-cols-2 gap-3">
            <a href="/production-orders/create"
                class="card bg-blue-700 text-white p-4 flex flex-col gap-2 active:scale-95 transition-transform">
                <span class="text-2xl">➕</span>
                <span class="font-bold text-base">Nueva orden</span>
            </a>
            <a href="/orders"
                class="card bg-white p-4 flex flex-col gap-2 active:scale-95 transition-transform">
                <span class="text-2xl">📋</span>
                <span class="font-bold text-base text-gray-800">Ver órdenes</span>
            </a>
            <a href="/clients/create"
                class="card bg-white p-4 flex flex-col gap-2 active:scale-95 transition-transform">
                <span class="text-2xl">👤</span>
                <span class="font-bold text-base text-gray-800">Nuevo cliente</span>
            </a>
            <a href="/clients"
                class="card bg-white p-4 flex flex-col gap-2 active:scale-95 transition-transform">
                <span class="text-2xl">🔍</span>
                <span class="font-bold text-base text-gray-800">Ver clientes</span>
            </a>
        </div>
    </div>

</div>
</x-app-layout>