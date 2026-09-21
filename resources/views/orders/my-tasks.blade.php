<x-app-layout title="Mis Tareas">

<div class="pt-4 space-y-5">

    {{-- HEADER --}}
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Mis tareas</h1>
            <p class="text-sm text-gray-500">Desliza una tarjeta hacia la derecha para avanzarla</p>
        </div>

        {{-- Botón Calendario — mismo estilo/posición que en orders/index.blade.php (Admin) --}}
        <a href="/production-orders/calendar"
        class="flex items-center gap-2 bg-indigo-600 text-white font-bold px-4 py-2.5 rounded-2xl text-sm shadow-md shadow-indigo-100 active:scale-95 transition-all shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5m-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            Calendario
        </a>
    </div>

    {{-- EMPTY STATE --}}
    @if($myOrders->count() === 0)

        <div class="text-center py-24 bg-gray-50 rounded-[2rem] border border-dashed border-gray-200">
            <div class="text-5xl mb-3">✓</div>
            <div class="text-xl font-bold text-gray-400">Todo al día</div>
            <div class="text-sm text-gray-400 mt-1">No tienes tareas pendientes</div>
        </div>

    @else

        <div class="space-y-4">
            @foreach($myOrders as $order)
                @include('orders.partials.task-card', ['order' => $order])
            @endforeach
        </div>

    @endif

</div>

@include('orders.partials.swipe-script')
</x-app-layout>
