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
        class="block bg-white rounded-2xl border border-gray-100 hover:border-gray-200 transition-all overflow-hidden">

            {{-- FILA SUPERIOR --}}
            <div class="flex items-stretch border-b border-gray-100">

                {{-- NÚMERO DE ORDEN --}}
                <div class="flex flex-col items-center justify-center px-5 py-4 bg-gray-50 border-r border-gray-100 min-w-[88px]">
                    <span class="text-[11px] text-gray-400 uppercase tracking-widest">Ord.</span>
                    <span class="text-2xl font-medium text-gray-900 leading-tight">
                        #{{ str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                {{-- PRODUCTO Y CLIENTE --}}
                <div class="flex flex-col justify-center px-5 py-4 flex-1 gap-0.5">
                    <p class="text-lg font-medium text-gray-900">{{ $order->product->name }}</p>
                    <p class="text-sm text-gray-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                        </svg>
                        {{ $order->client->full_name }}
                        <span class="text-gray-300">·</span>
                        {{ $order->client->phone }}
                    </p>
                    <p class="text-xs text-gray-300 flex items-center gap-1.5 ml-0.5">
                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        {{ $order->client->address }}, {{ $order->client->city->name }}, {{ $order->client->department->name }}
                    </p>
                </div>

                {{-- ETAPA --}}
                <div class="flex items-center gap-2.5 px-5 py-4 border-l border-gray-100">
                    @if($order->currentStage)
                        <span class="w-2.5 h-2.5 rounded-full shrink-0"
                            style="background: {{ $order->currentStage->color }}"></span>
                        <span class="text-sm font-medium"
                            style="color: {{ $order->currentStage->color }}">
                            {{ $order->currentStage->name }}
                        </span>
                    @else
                        <span class="w-2.5 h-2.5 rounded-full bg-gray-300 shrink-0"></span>
                        <span class="text-sm font-medium text-gray-400">Sin etapa</span>
                    @endif
                </div>

            </div>

            {{-- FILA INFERIOR --}}
            <div class="flex items-stretch flex-wrap">

                {{-- COLOR --}}
                <div class="flex items-center gap-2 px-[18px] py-3 text-sm text-gray-600 border-r border-gray-100">
                    <span class="w-3 h-3 rounded-full border border-gray-300 shrink-0"
                        style="background: {{ $order->color }}"></span>
                    {{ $order->color }}
                </div>

                {{-- CALCOMANÍAS --}}
                @if($order->sticker_color)
                    <div class="flex items-center gap-2 px-[18px] py-3 text-sm font-medium text-amber-700 bg-amber-50 border-r border-amber-200">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L9.568 3z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                        </svg>
                        Calcomanía {{ $order->sticker_color }}
                    </div>
                @else
                    <div class="flex items-center gap-2 px-[18px] py-3 text-sm text-gray-400 border-r border-gray-100">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L9.568 3z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                        </svg>
                        Sin calcomanías
                    </div>
                @endif

                {{-- PIEZAS --}}
                <div class="flex items-center gap-2 px-[18px] py-3 text-sm text-gray-600 border-r border-gray-100">
                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                    </svg>
                    {{ $order->product->pieces ?? 0 }} piezas
                </div>

                {{-- FECHA --}}
                @if($order->due_date)
                    <div class="flex items-center gap-2 px-[18px] py-3 text-sm border-r border-gray-100
                        {{ $order->due_date->isPast() ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                        <svg class="w-4 h-4 shrink-0 {{ $order->due_date->isPast() ? 'text-red-400' : 'text-gray-400' }}"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                        </svg>
                        {{ $order->due_date->format('d/m') }}
                        @if($order->due_date->isPast()) — Vencida @endif
                    </div>
                @endif

                {{-- PRECIO Y SALDO (derecha) --}}
                <div class="ml-auto flex flex-col items-end justify-center px-5 py-3 gap-0.5">
                    @php $balance = $order->price - $order->payments->sum('amount'); @endphp

                    <span class="text-base font-medium text-gray-900">
                        ${{ number_format($order->price, 0, ',', '.') }}
                    </span>

                    @if($balance > 0)
                        <span class="text-xs text-red-600">
                            Debe ${{ number_format($balance, 0, ',', '.') }}
                        </span>
                    @else
                        <span class="text-xs text-green-700 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Pagado
                        </span>
                    @endif
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