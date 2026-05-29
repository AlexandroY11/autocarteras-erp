<x-app-layout title="Productos">
    <div class="flex justify-between items-center mt-4 mb-3">
        <h1 class="text-lg font-bold text-gray-800">Productos</h1>
        <a href="/products/create"
            class="bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">+ Nuevo</a>
    </div>

    <form method="GET" action="/products" class="mb-3">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Buscar producto..."
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </form>

    <div class="space-y-3">
        @forelse($products as $product)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-semibold text-gray-800">{{ $product->name }}</div>
                    <div class="text-sm text-gray-500 mt-0.5">
                        {{ $product->pieces }} piezas · {{ $product->avg_production_days }} días
                    </div>
                    <div class="text-sm font-medium text-blue-700 mt-1">
                        ${{ number_format($product->base_price, 0, ',', '.') }}
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="/products/{{ $product->id }}/edit"
                        class="text-xs bg-gray-100 px-3 py-1 rounded-lg text-gray-600">Editar</a>
                    <form method="POST" action="/products/{{ $product->id }}"
                        onsubmit="return confirm('¿Eliminar este producto?')">
                        @csrf @method('DELETE')
                        <button class="text-xs bg-red-50 px-3 py-1 rounded-lg text-red-600">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-gray-400 py-12">
            <div class="text-4xl mb-2">📦</div>
            <div>No hay productos</div>
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
</x-app-layout>