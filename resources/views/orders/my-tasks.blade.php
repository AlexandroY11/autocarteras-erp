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

    {{-- SEARCH — mismo estilo que orders/index.blade.php (Admin), sin filtro
         de etapa/fecha: aquí solo busca dentro de lo que el usuario ya ve
         (tareas de su habilidad, no terminadas/canceladas) --}}
    <div class="bg-gray-50 border border-gray-200 rounded-[2rem] p-4 shadow-inner">
        <form method="GET" action="/orders" class="relative">

            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Buscar cliente, producto o número de orden..."
                   class="w-full border-none rounded-2xl px-5 py-3.5 pl-12 text-sm focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
        </form>
    </div>

    {{-- EMPTY STATE --}}
    @if($myOrders->count() === 0)

        <div class="text-center py-24 bg-gray-50 rounded-[2rem] border border-dashed border-gray-200">
            @if(request('search'))
                <div class="text-5xl mb-3">🔍</div>
                <div class="text-xl font-bold text-gray-400">Sin resultados</div>
                <div class="text-sm text-gray-400 mt-1">Ninguna tarea tuya coincide con "{{ request('search') }}"</div>
            @else
                <div class="text-5xl mb-3">✓</div>
                <div class="text-xl font-bold text-gray-400">Todo al día</div>
                <div class="text-sm text-gray-400 mt-1">No tienes tareas pendientes</div>
            @endif
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
