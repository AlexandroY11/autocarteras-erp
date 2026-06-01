<x-app-layout :title="isset($product) ? 'Editar Producto' : 'Nuevo Producto'">
<div class="max-w-2xl mx-auto pt-4 pb-12">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="/products" class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-blue-600 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">
                {{ isset($product) ? 'Editar Producto' : 'Nuevo Producto' }}
            </h1>
            <p class="text-sm text-gray-500 font-medium">Define las características de tu catálogo</p>
        </div>
    </div>

    <form method="POST" action="{{ isset($product) ? "/products/{$product->id}" : "/products" }}" class="space-y-6">
        @csrf
        @if(isset($product)) @method('PUT') @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 space-y-6">
            {{-- Nombre --}}
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Nombre del Producto *</label>
                <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
                    placeholder="Ej: Cartera Premium XL"
                    class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Piezas --}}
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Número de Piezas *</label>
                    <input type="number" name="pieces" value="{{ old('pieces', $product->pieces ?? '') }}" required
                        placeholder="0"
                        class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
                </div>

                {{-- Días de producción --}}
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Días de Producción *</label>
                    <input type="number" name="avg_production_days" value="{{ old('avg_production_days', $product->avg_production_days ?? '') }}" required
                        placeholder="0"
                        class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
                </div>
            </div>

            {{-- Precio --}}
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Precio Base de Venta *</label>
                <div class="relative">
                    <span class="absolute left-5 top-1/2 -translate-y-1/2 font-bold text-gray-400">$</span>
                    <input type="number" name="base_price" value="{{ old('base_price', $product->base_price ?? '') }}" required step="1000"
                        class="w-full bg-gray-50 border-none rounded-2xl px-10 py-5 text-xl font-black text-blue-700 focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-blue-700 hover:bg-blue-800 text-white font-black py-5 rounded-[2rem] text-lg transition-all shadow-xl shadow-blue-100 active:scale-[0.98]">
            {{ isset($product) ? 'Actualizar Producto' : 'Guardar en Catálogo' }}
        </button>
    </form>
</div>
</x-app-layout>
