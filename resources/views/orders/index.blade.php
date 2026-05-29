<x-app-layout title="{{ $order->exists ? 'Editar Orden' : 'Nueva Orden' }}">
    <div class="flex items-center gap-3 mt-4 mb-4">
        <a href="/dashboard" class="text-gray-500">← Volver</a>
        <h1 class="text-lg font-bold text-gray-800">
            {{ $order->exists ? 'Editar Orden #'.str_pad($order->consecutive, 3, '0', STR_PAD_LEFT) : 'Nueva Orden' }}
        </h1>
    </div>

    <form method="POST"
        action="{{ $order->exists ? '/production-orders/'.$order->id : '/production-orders' }}"
        class="space-y-4 bg-white rounded-xl p-4 shadow-sm"
        x-data="{ sticker: {{ old('sticker', $order->sticker ?? false) ? 'true' : 'false' }} }">
        @csrf
        @if($order->exists) @method('PUT') @endif

        {{-- Cliente --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
            <select name="client_id" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar cliente...</option>
                @foreach($clients as $client)
                <option value="{{ $client->id }}"
                    {{ old('client_id', $order->client_id) == $client->id ? 'selected' : '' }}>
                    {{ $client->full_name }} — {{ $client->phone }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Producto --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Producto *</label>
            <select name="product_id" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar producto...</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}"
                    {{ old('product_id', $order->product_id) == $product->id ? 'selected' : '' }}>
                    {{ $product->name }} — ${{ number_format($product->base_price, 0, ',', '.') }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Color --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Color *</label>
            <input type="text" name="color" value="{{ old('color', $order->color) }}"
                placeholder="Ej: Negro, Gris, Blanco..." required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Calcomanía --}}
        <div class="border border-gray-200 rounded-xl p-4 space-y-3">
            <div class="flex items-center gap-3">
                <input type="checkbox" name="sticker" id="sticker" value="1"
                    x-model="sticker"
                    {{ old('sticker', $order->sticker ?? false) ? 'checked' : '' }}
                    class="w-4 h-4 text-blue-600">
                <label for="sticker" class="text-sm font-medium text-gray-700">¿Lleva calcomanía?</label>
            </div>
            <div x-show="sticker" x-transition>
                <label class="block text-sm font-medium text-gray-700 mb-1">Color de calcomanía</label>
                <input type="text" name="sticker_color" value="{{ old('sticker_color', $order->sticker_color) }}"
                    placeholder="Ej: Rojo, Azul, Dorado..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- Precio y anticipo --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Precio *</label>
                <input type="number" name="price" value="{{ old('price', $order->price) }}"
                    min="0" step="1000" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Anticipo</label>
                <input type="number" name="advance_payment" value="{{ old('advance_payment', $order->advance_payment ?? 30000) }}"
                    min="0" step="1000"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- Fecha compromiso --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha compromiso *</label>
            <input type="date" name="due_date"
                value="{{ old('due_date', $order->due_date?->format('Y-m-d')) }}" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Observaciones --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
            <textarea name="observations" rows="3"
                placeholder="Detalles especiales, instrucciones del cliente..."
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('observations', $order->observations) }}</textarea>
        </div>

        <button type="submit"
            class="w-full bg-blue-700 text-white font-semibold py-3 rounded-xl">
            {{ $order->exists ? 'Guardar cambios' : 'Crear orden' }}
        </button>
    </form>
</x-app-layout>