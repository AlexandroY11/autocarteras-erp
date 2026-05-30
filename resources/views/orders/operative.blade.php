<x-app-layout title="Mis Tareas">
<div class="pt-4">

    <div class="mb-4">
        <h1 class="text-2xl font-black text-gray-900">Mis tareas</h1>
        <p class="text-sm text-gray-500">Órdenes que puedes avanzar</p>
    </div>

    @if($myOrders->count() === 0)
    <div class="text-center py-20">
        <div class="text-7xl mb-4">✅</div>
        <div class="text-xl font-bold text-gray-400">¡Todo al día!</div>
        <div class="text-sm text-gray-400 mt-1">No tienes tareas pendientes</div>
    </div>
    @else
    <div class="space-y-3">
        @foreach($myOrders as $order)
        <div class="bg-white card p-4">
            {{-- Header --}}
            <div class="flex justify-between items-start mb-3">
                <div>
                    <span class="text-xs font-bold text-gray-400">#{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}</span>
                    <div class="text-xl font-black text-gray-900">{{ $order->client->full_name }}</div>
                    <div class="text-sm text-gray-500">{{ $order->product->name }}</div>
                </div>
                @if($order->due_date->isPast())
                <span class="text-xs bg-red-100 text-red-700 font-bold px-3 py-1 rounded-full">⚠ Vencida</span>
                @else
                <span class="text-xs bg-gray-100 text-gray-600 font-semibold px-3 py-1 rounded-full">
                    {{ $order->due_date->diffForHumans() }}
                </span>
                @endif
            </div>

            {{-- Detalles clave --}}
            <div class="grid grid-cols-2 gap-2 mb-4">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="text-xs text-gray-400 font-semibold">Color</div>
                    <div class="text-base font-bold text-gray-800 flex items-center gap-2 mt-1">
                        <span class="w-4 h-4 rounded-full border border-gray-300"
                            style="background: {{ strtolower($order->color) === 'negro' ? '#111' : (strtolower($order->color) === 'gris' ? '#9ca3af' : '#f5f0e8') }}">
                        </span>
                        {{ $order->color }}
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="text-xs text-gray-400 font-semibold">Calcomanía</div>
                    <div class="text-base font-bold text-gray-800 mt-1">
                        {{ $order->sticker ? '✅ '.$order->sticker_color : 'No' }}
                    </div>
                </div>
            </div>

            @if($order->observations)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-4">
                <div class="text-xs font-bold text-yellow-700 mb-1">📝 Observaciones</div>
                <div class="text-sm text-yellow-800">{{ $order->observations }}</div>
            </div>
            @endif

            {{-- Etapa actual --}}
            <div class="flex items-center gap-2 mb-4">
                <div class="text-xs font-semibold text-gray-500">Etapa actual:</div>
                @if($order->currentStage)
                <span class="text-xs font-bold text-white px-3 py-1 rounded-full"
                    style="background: {{ $order->currentStage->color }}">
                    {{ $order->currentStage->name }}
                </span>
                @endif
            </div>

            {{-- Botón avanzar --}}
            @if($order->currentStage && auth()->user()->canAdvanceStage($order->currentStage->id))
            <form method="POST" action="/production-orders/{{ $order->id }}/advance-stage"
                x-data="{ loading: false }"
                @submit="loading = true">
                @csrf
                <button type="submit"
                    :disabled="loading"
                    class="w-full py-4 rounded-2xl font-black text-lg text-white flex items-center justify-center gap-2 transition-all active:scale-95"
                    style="background: {{ $order->currentStage->color }}"
                    :class="loading ? 'opacity-70' : ''">
                    <span x-show="!loading">✓ Marcar como completada</span>
                    <span x-show="loading">Guardando...</span>
                </button>
            </form>
            @endif
        </div>
        @endforeach
    </div>
    @endif

</div>
</x-app-layout>