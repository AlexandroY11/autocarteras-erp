<x-app-layout title="Productos">
<div class="pt-4 space-y-5">

    {{-- 1. HEADER --}}
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Productos</h1>
            <p class="text-sm font-medium text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded-lg mt-1">
                Catálogo de producción
            </p>
        </div>
        <a href="/products/create"
            class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm active:scale-95 transition-all shadow-md shadow-blue-100">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nuevo Producto
        </a>
    </div>

    {{-- 2. BUSCADOR --}}
    <form method="GET" action="/products" class="relative">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Buscar por nombre de producto..."
            class="w-full border-none rounded-2xl px-5 py-4 pl-12 text-sm focus:ring-2 focus:ring-blue-500 shadow-sm bg-white">
        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
    </form>

    {{-- 3. LISTADO DE PRODUCTOS --}}
    <div class="grid grid-cols-1 gap-3">
        @forelse($products as $product)
        <div class="bg-white rounded-[2rem] p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
            
            {{-- Decoración sutil --}}
            <div class="absolute top-0 right-0 w-24 h-24 bg-gray-50 rounded-full -mr-12 -mt-12 transition-transform group-hover:scale-110"></div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative">
                <div class="flex items-center gap-4">
                    {{-- Icono representativo --}}
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ $product->name }}</h3>
                        <div class="flex items-center gap-3 mt-1">
                            <span class="flex items-center gap-1 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z" />
                                </svg>
                                {{ $product->pieces }} piezas
                            </span>
                            <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                            <span class="flex items-center gap-1 text-xs font-bold text-gray-400 uppercase tracking-wider">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $product->avg_production_days }} días prom.
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between md:justify-end gap-6">
                    {{-- Precio --}}
                    <div class="text-right">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Precio Base</p>
                        <p class="text-xl font-black text-blue-700">
                            ${{ number_format($product->base_price, 0, ',', '.') }}
                        </p>
                    </div>

                    {{-- Acciones --}}
                    <div class="flex gap-2">
                        <a href="/products/{{ $product->id }}/edit" 
                           class="p-2.5 bg-gray-50 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                            </svg>
                        </a>
                        <form id="delete-form-{{ $product->id }}" method="POST" action="/products/{{ $product->id }}">
                            @csrf @method('DELETE')
                            <button type="button" 
                                @click="confirmDelete('delete-form-{{ $product->id }}', '¿Deseas eliminar el producto {{ $product->name }}?')"
                                class="p-2.5 bg-gray-50 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-20 bg-white rounded-[2.5rem] border border-gray-100 shadow-sm">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-300">No hay productos registrados</h3>
            <p class="text-gray-400 text-sm">Comienza agregando uno nuevo al catálogo.</p>
        </div>
        @endforelse
    </div>

    {{-- 4. PAGINACIÓN --}}
    <div class="mt-6">{{ $products->links() }}</div>
</div>
</x-app-layout>
