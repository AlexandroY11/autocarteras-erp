<x-app-layout title="{{ $product->exists ? 'Editar Producto' : 'Nuevo Producto' }}">
    <div class="flex items-center gap-3 mt-4 mb-4">
        <a href="/products" class="text-gray-500">← Volver</a>
        <h1 class="text-lg font-bold text-gray-800">
            {{ $product->exists ? 'Editar Producto' : 'Nuevo Producto' }}
        </h1>
    </div>

    <form method="POST"
        action="{{ $product->exists ? '/products/'.$product->id : '/products' }}"
        class="space-y-4 bg-white rounded-xl p-4 shadow-sm">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea name="description" rows="3"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Piezas</label>
                <input type="number" name="pieces" value="{{ old('pieces', $product->pieces ?? 1) }}" min="1"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Días producción</label>
                <input type="number" name="avg_production_days" value="{{ old('avg_production_days', $product->avg_production_days ?? 7) }}" min="1"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Precio base *</label>
            <input type="number" name="base_price" value="{{ old('base_price', $product->base_price) }}" min="0" step="1000" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        @if($product->exists)
        <div class="flex items-center gap-2">
            <input type="checkbox" name="active" id="active" value="1"
                {{ old('active', $product->active) ? 'checked' : '' }}
                class="w-4 h-4 text-blue-600">
            <label for="active" class="text-sm text-gray-700">Producto activo</label>
        </div>
        @endif

        <button type="submit"
            class="w-full bg-blue-700 text-white font-semibold py-3 rounded-xl">
            {{ $product->exists ? 'Guardar cambios' : 'Crear producto' }}
        </button>
    </form>
</x-app-layout>