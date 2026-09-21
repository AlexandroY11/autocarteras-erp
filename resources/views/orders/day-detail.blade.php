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
                        {{ $orders->count() }} órdenes con fecha compromiso este día
                    </p>
                </div>
            </div>
        </div>

        {{-- LISTADO DE ÓRDENES --}}
        <div class="px-4 space-y-4">
            @forelse($orders as $order)
                @include('orders.partials.task-card', ['order' => $order])
            @empty
                <div class="text-center py-20 bg-white rounded-[2.5rem] border border-dashed border-gray-200">
                    <h3 class="text-xl font-bold text-gray-400">Sin órdenes</h3>
                    <p class="text-sm text-gray-400 mt-1">No hay pedidos con fecha compromiso de producción este día</p>
                </div>
            @endforelse
        </div>
    </div>

    @include('orders.partials.swipe-script')
</x-app-layout>
