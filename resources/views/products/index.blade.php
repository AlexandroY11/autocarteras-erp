<x-app-layout title="Productos">
<div class="pt-4 space-y-5" x-data="{ showFilters: false }">

    {{-- Header --}}
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Productos</h1>
            <p class="text-sm font-medium text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded-lg mt-1">
                {{ $products->total() }} productos en catálogo
            </p>
        </div>
        <div class="flex gap-2">
            <button @click="showFilters = !showFilters" 
                class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-bold px-4 py-2.5 rounded-2xl text-sm active:scale-95 transition-all shadow-sm">
                <svg class="w-4 h-4" :class="showFilters ? 'text-blue-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                Filtros
            </button>
            <a href="/products/create"
                class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm active:scale-95 transition-all shadow-md shadow-blue-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nuevo
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div x-show="showFilters" x-transition class="bg-gray-50 border border-gray-200 rounded-3xl p-5 shadow-inner">
        <form method="GET" action="/products" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar por nombre..."
                    class="w-full border-none rounded-2xl px-5 py-3.5 pl-12 text-sm focus:ring-2 focus:ring-blue-500 shadow-sm">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
            </div>
            <div class="flex gap-2">
                <select name="pieces" class="flex-1 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 shadow-sm">
                    <option value="">Cualquier pieza</option>
                    <option value="1-5" {{ request('pieces') == '1-5' ? 'selected' : '' }}>1 a 5 piezas</option>
                    <option value="6-10" {{ request('pieces') == '6-10' ? 'selected' : '' }}>6 a 10 piezas</option>
                    <option value="11+" {{ request('pieces') == '11+' ? 'selected' : '' }}>Más de 10</option>
                </select>
                <button type="submit" class="bg-blue-700 text-white px-6 rounded-2xl font-bold text-sm">Filtrar</button>
            </div>
        </form>
    </div>

    {{-- Stats Rápidas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Productos</p>
            <p class="text-2xl font-black text-gray-900">{{ $products->total() }}</p>
        </div>
        <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Promedio Días</p>
            <p class="text-2xl font-black text-blue-600">{{ round($products->avg('avg_production_days'), 1) }}</p>
        </div>
    </div>

    {{-- Listado --}}
    <div class="grid grid-cols-1 gap-3">
        @forelse($products as $product)
        <div class="bg-white rounded-[2rem] p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
            <div class="flex items-center justify-between gap-4 relative">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                        <span class="text-blue-600 font-black text-lg">{{ $product->pieces }}</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ $product->name }}</h3>
                        <p class="text-xs font-bold text-gray-400 uppercase mt-1">{{ $product->avg_production_days }} días de producción</p>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="text-[10px] font-black text-gray-400 uppercase leading-none mb-1">Precio</p>
                        <p class="text-xl font-black text-blue-700">${{ number_format($product->base_price, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="/products/{{ $product->id }}/edit" class="p-2.5 bg-gray-50 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                        </a>
                        <form id="delete-form-{{ $product->id }}" method="POST" action="/products/{{ $product->id }}">
                            @csrf @method('DELETE')
                            <button type="button" @click="confirmDelete('delete-form-{{ $product->id }}', '¿Eliminar {{ $product->name }}?')"
                                class="p-2.5 bg-gray-50 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-20 bg-gray-50 rounded-[2.5rem] border-2 border-dashed border-gray-200">
            <h3 class="text-xl font-bold text-gray-400">No hay productos</h3>
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</div>
</x-app-layout>
