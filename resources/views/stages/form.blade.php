<x-app-layout title="{{ $stage->exists ? 'Editar Etapa' : 'Nueva Etapa' }}">
    <div class="flex items-center gap-3 mt-4 mb-4">
        <a href="/stages" class="text-gray-500">← Volver</a>
        <h1 class="text-lg font-bold text-gray-800">
            {{ $stage->exists ? 'Editar Etapa' : 'Nueva Etapa' }}
        </h1>
    </div>

    <form method="POST"
        action="{{ $stage->exists ? '/stages/'.$stage->id : '/stages' }}"
        class="space-y-4 bg-white rounded-xl p-4 shadow-sm">
        @csrf
        @if($stage->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
            <input type="text" name="name" value="{{ old('name', $stage->name) }}" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Orden *</label>
            <input type="number" name="order" value="{{ old('order', $stage->order) }}" min="1" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Color (hex)</label>
            <div class="flex gap-3 items-center">
                <input type="color" name="color" value="{{ old('color', $stage->color ?? '#6B7280') }}"
                    class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                <span class="text-sm text-gray-500">Selecciona el color de la etiqueta</span>
            </div>
        </div>

        @if($stage->exists)
        <div class="flex items-center gap-2">
            <input type="checkbox" name="active" id="active" value="1"
                {{ old('active', $stage->active) ? 'checked' : '' }}
                class="w-4 h-4 text-blue-600">
            <label for="active" class="text-sm text-gray-700">Etapa activa</label>
        </div>
        @endif

        <button type="submit"
            class="w-full bg-blue-700 text-white font-semibold py-3 rounded-xl">
            {{ $stage->exists ? 'Guardar cambios' : 'Crear etapa' }}
        </button>
    </form>
</x-app-layout>