<x-app-layout title="Gastos de Materiales">
<div class="pt-4 space-y-4">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Materiales</h1>
            <p class="text-sm text-gray-500">Libro de compras</p>
        </div>
        <a href="/material-purchases/create"
            class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm">
            + Registrar
        </a>
    </div>

    {{-- Filtros de período --}}
    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach(['week' => 'Esta semana', 'biweekly' => 'Quincena', 'month' => 'Este mes', 'all' => 'Todo'] as $key => $label)
        <a href="/material-purchases?period={{ $key }}"
            class="shrink-0 px-4 py-2 rounded-full text-sm font-semibold border transition-all
            {{ $period === $key ? 'bg-blue-700 text-white border-blue-700' : 'bg-white text-gray-600 border-gray-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Total del período --}}
    <div class="bg-blue-700 card p-5 text-white">
        <div class="text-sm font-semibold opacity-80">Total gastado</div>
        <div class="text-4xl font-black mt-1">${{ number_format($totalPeriod, 0, ',', '.') }}</div>
        <div class="text-sm opacity-70 mt-1">{{ $purchases->count() }} compras registradas</div>
    </div>

    {{-- Por material --}}
    @if($byMaterial->count() > 0)
    <div class="bg-white card p-4">
        <p class="section-title mb-3">Gasto por material</p>
        <div class="space-y-3">
            @foreach($byMaterial->sortByDesc('total') as $name => $data)
            <div class="flex justify-between items-center">
                <div>
                    <div class="text-sm font-bold text-gray-800">{{ $name }}</div>
                    <div class="text-xs text-gray-400">{{ number_format($data['qty'], 2) }} unidades</div>
                </div>
                <div class="text-sm font-black text-gray-900">${{ number_format($data['total'], 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Lista de compras --}}
    <div>
        <p class="section-title mb-3">Compras</p>
        <div class="space-y-3">
            @forelse($purchases as $purchase)
            <div class="bg-white card p-4">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="text-base font-bold text-gray-900">{{ $purchase->material->name }}</div>
                        @if($purchase->supplier)
                        <div class="text-sm text-gray-500">{{ $purchase->supplier->name }}</div>
                        @endif
                        <div class="text-sm text-gray-500 mt-1">
                            {{ number_format($purchase->quantity, 2) }} {{ $purchase->material->unit }}
                            · ${{ number_format($purchase->unit_price, 0, ',', '.') }}/{{ $purchase->material->unit }}
                        </div>
                        @if($purchase->notes)
                        <div class="text-xs text-gray-400 mt-1">{{ $purchase->notes }}</div>
                        @endif
                        <div class="text-xs text-gray-400 mt-1">{{ $purchase->purchased_at->format('d/m/Y') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-black text-gray-900">${{ number_format($purchase->total, 0, ',', '.') }}</div>
                        @if(auth()->user()->isAdmin())
                        <form method="POST" action="/material-purchases/{{ $purchase->id }}"
                            onsubmit="return confirm('¿Eliminar esta compra?')" class="mt-2">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 font-semibold">Eliminar</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🧴</div>
                <div class="text-xl font-bold text-gray-400">Sin compras registradas</div>
            </div>
            @endforelse
        </div>
    </div>

</div>
</x-app-layout>