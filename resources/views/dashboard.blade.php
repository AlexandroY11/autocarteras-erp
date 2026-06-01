<x-app-layout title="Dashboard">
<div class="pt-4 space-y-6">

    {{-- 1. HEADER & RESUMEN RÁPIDO --}}
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Panel de Control</h1>
            <p class="text-sm font-medium text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded-lg mt-1">
                Resumen de producción y finanzas
            </p>
        </div>
        <div class="text-right">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Hoy</p>
            <p class="text-sm font-bold text-gray-700">{{ now()->format('d M, Y') }}</p>
        </div>
    </div>

    {{-- 2. STATS PRINCIPALES (GRID) --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @php
            $statCards = [
                ['label' => 'Pendientes', 'value' => $stats['pending'], 'color' => 'blue', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'En Proceso', 'value' => $stats['in_progress'], 'color' => 'purple', 'icon' => 'M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.96 5.96m0 0L2.25 21l.81-2.25 5.96-5.96'],
                ['label' => 'Terminados', 'value' => $stats['done'], 'color' => 'emerald', 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Entregados', 'value' => $stats['delivered'], 'color' => 'gray', 'icon' => 'M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15m0-3l-3-3m0 0l-3 3m3-3V15'],
                ['label' => 'Vencidos', 'value' => $stats['overdue'], 'color' => 'red', 'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z'],
            ];
        @endphp

        @foreach($statCards as $card)
        <div class="bg-white p-5 rounded-[2rem] border border-gray-100 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-16 h-16 bg-{{ $card['color'] }}-50 rounded-full -mr-8 -mt-8 transition-transform group-hover:scale-110"></div>
            <div class="relative">
                <div class="w-10 h-10 bg-{{ $card['color'] }}-50 rounded-xl flex items-center justify-center text-{{ $card['color'] }}-600 mb-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                    </svg>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">{{ $card['label'] }}</p>
                <p class="text-2xl font-black text-gray-900">{{ $card['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 3. FINANZAS Y RENDIMIENTO --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Ingresos del Mes --}}
        <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50/50 rounded-full -mr-16 -mt-16"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75m0 1.5v.75m0 1.5v.75m0 1.5V15m1.5 1.5h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m-1.5-1.5V4.5m0 12h-15" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider">Ingresos del Mes</h2>
                </div>
                <p class="text-3xl font-black text-emerald-600">${{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
                <p class="text-xs font-bold text-gray-400 mt-1">Recaudado en {{ now()->format('F') }}</p>
            </div>
        </div>

        {{-- Órdenes del Mes --}}
        <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 rounded-full -mr-16 -mt-16"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider">Nuevas Órdenes</h2>
                </div>
                <p class="text-3xl font-black text-blue-600">{{ $monthlyOrders }}</p>
                <p class="text-xs font-bold text-gray-400 mt-1">Registradas este mes</p>
            </div>
        </div>

        {{-- Saldo Pendiente --}}
        <div class="bg-gray-900 p-6 rounded-[2.5rem] shadow-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -mr-16 -mt-16"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-black text-gray-300 uppercase tracking-wider">Cartera Total</h2>
                </div>
                <p class="text-3xl font-black text-white">${{ number_format($totalPending, 0, ',', '.') }}</p>
                <p class="text-xs font-bold text-gray-500 mt-1">Saldo pendiente por cobrar</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- TOP CIUDADES --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
            <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                Top Ciudades
            </h3>
            <div class="space-y-4">
                @foreach($topCities as $city => $total)
                <div>
                    <div class="flex justify-between text-sm font-bold mb-1">
                        <span class="text-gray-700">{{ $city }}</span>
                        <span class="text-blue-600">{{ $total }} órdenes</span>
                    </div>
                    <div class="w-full bg-gray-50 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($total / max($topCities->toArray())) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ÓRDENES POR ETAPA --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
            <h3 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                <span class="w-2 h-6 bg-purple-600 rounded-full"></span>
                Órdenes por Etapa
            </h3>
            <div class="space-y-4">
                @foreach($byStage as $stage)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-xs font-black text-purple-600 shadow-sm">
                            {{ $stage->order }}
                        </span>
                        <span class="text-sm font-bold text-gray-700">{{ $stage->name }}</span>
                    </div>
                    <span class="bg-purple-100 text-purple-700 text-xs font-black px-3 py-1 rounded-full">
                        {{ $stage->orders_count }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 4. ÓRDENES VENCIDAS (ESTILO FILA) --}}
    <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <span class="w-2 h-6 bg-red-600 rounded-full"></span>
                Órdenes Vencidas
            </h3>
            <span class="text-xs font-bold text-red-600 bg-red-50 px-3 py-1 rounded-full uppercase tracking-wider">Atención Prioritaria</span>
        </div>

        <div class="space-y-3">
            @forelse($overdueOrders as $order)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-3xl border border-transparent hover:border-red-100 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                        <span class="text-red-600 font-black text-lg">!</span>
                    </div>
                    <div>
                        <p class="text-sm font-black text-gray-900 leading-none mb-1">{{ $order->client->full_name }}</p>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-tighter">
                            {{ $order->product->name }} · <span class="text-red-500">Venció {{ $order->due_date->diffForHumans() }}</span>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-gray-400 uppercase leading-none mb-1">Etapa Actual</p>
                    <span class="text-xs font-black text-gray-700 bg-white px-3 py-1 rounded-lg shadow-sm">
                        {{ $order->currentStage->name }}
                    </span>
                </div>
            </div>
            @empty
            <div class="text-center py-10">
                <p class="text-sm font-bold text-gray-400">No hay órdenes vencidas. ¡Excelente trabajo!</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
</x-app-layout>
