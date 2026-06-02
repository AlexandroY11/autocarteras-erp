<x-app-layout title="Órdenes">
<div class="pt-4 space-y-5">

    {{-- HEADER --}}
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Órdenes</h1>
            <p class="text-sm font-medium text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded-lg mt-1">
                {{ $orders->total() }} órdenes registradas
            </p>
        </div>

        <a href="/production-orders/create"
           class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm shadow-md shadow-blue-100 active:scale-95 transition-all">

            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>

            Nueva
        </a>
    </div>

    {{-- SEARCH + FILTER --}}
    <div class="bg-gray-50 border border-gray-200 rounded-[2rem] p-4 shadow-inner">

        <form method="GET" action="/orders" class="flex flex-col md:flex-row gap-3">

            {{-- SEARCH --}}
            <div class="flex-1 relative">

                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>

                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Buscar cliente o número de orden..."
                       class="w-full border-none rounded-2xl px-5 py-3.5 pl-12 text-sm focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
            </div>

            {{-- STAGE FILTER --}}
            <select name="stage"
                    class="w-full md:w-64 border-none rounded-2xl px-4 py-3.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 shadow-sm">

                <option value="">Todas las etapas</option>

                @foreach($stages as $stage)
                    <option value="{{ $stage->id }}" @selected(request('stage') == $stage->id)>
                        {{ $stage->name }}
                    </option>
                @endforeach

            </select>

            <button class="bg-blue-700 text-white px-8 rounded-2xl font-black text-sm shadow-md shadow-blue-100">
                Buscar
            </button>

        </form>
    </div>

    {{-- LIST --}}
    <div class="space-y-3">

        @forelse($orders as $order)
        <a href="/production-orders/{{ $order->id }}"
           class="block bg-white rounded-[2rem] p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

                {{-- LEFT --}}
                <div class="flex items-center gap-5 flex-1 min-w-0">

                    {{-- ICON --}}
                    <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center border border-gray-100">

                        <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                        </svg>

                    </div>

                    {{-- INFO --}}
                    <div class="min-w-0">

                        <div class="flex items-center gap-2">

                            <p class="text-xs font-bold text-gray-400">
                                #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}
                            </p>

                            @if($order->currentStage)
                                <span class="text-xs font-bold text-white px-2 py-0.5 rounded-full"
                                      style="background: {{ $order->currentStage->color }}">
                                    {{ $order->currentStage->name }}
                                </span>
                            @else
                                <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">
                                    Sin etapa
                                </span>
                            @endif

                        </div>

                        <h3 class="font-bold text-gray-900 text-lg truncate">
                            {{ $order->client->full_name }}
                        </h3>

                        <p class="text-sm text-gray-500 truncate">
                            {{ $order->product->name }}
                        </p>

                        {{-- DETAILS --}}
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">

                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                                {{ $order->color }}
                            </span>

                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M20 12H4"/>
                                </svg>
                                {{ $order->product->pieces ?? 0 }} piezas
                            </span>

                            @if($order->due_date)
                                <span class="flex items-center gap-1 {{ $order->due_date->isPast() ? 'text-red-500 font-semibold' : '' }}">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M8 7V3m8 4V3M4 11h16M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $order->due_date->format('d/m') }}
                                </span>
                            @endif

                        </div>

                    </div>
                </div>

                {{-- RIGHT --}}
                <div class="text-right shrink-0">

                    <p class="text-[10px] font-bold text-gray-400 uppercase">Total</p>
                    <p class="text-xl font-black text-blue-700">
                        ${{ number_format($order->price, 0, ',', '.') }}
                    </p>

                    @php
                        $balance = $order->price - $order->payments->sum('amount');
                    @endphp

                    <p class="text-xs font-semibold mt-1 {{ $balance > 0 ? 'text-red-500' : 'text-green-600' }}">
                        {{ $balance > 0
                            ? "Debe $".number_format($balance,0,',','.')
                            : "Pagado"
                        }}
                    </p>

                </div>
            </div>

        </a>
        @empty

        <div class="text-center py-20 bg-gray-50 rounded-[2.5rem] border border-dashed border-gray-200">
            <h3 class="text-xl font-bold text-gray-400">No hay órdenes</h3>
            <p class="text-sm text-gray-400 mt-1">Crea la primera orden para comenzar</p>
        </div>

        @endforelse

    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $orders->links() }}
    </div>

</div>
</x-app-layout>