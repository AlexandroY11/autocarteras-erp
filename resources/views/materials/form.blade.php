<x-app-layout title="{{ $material->exists ? 'Editar Material' : 'Nuevo Material' }}">
<div class="pt-4">
    <div class="flex items-center gap-3 mb-6">
        <a href="/materials" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-black text-gray-900">
            {{ $material->exists ? 'Editar Material' : 'Nuevo Material' }}
        </h1>
    </div>

    <form method="POST"
        action="{{ $material->exists ? '/materials/'.$material->id : '/materials' }}"
        class="space-y-4">
        @csrf
        @if($material->exists) @method('PUT') @endif

        <div class="bg-white card p-4 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del material *</label>
                <input type="text" name="name" value="{{ old('name', $material->name) }}" required
                    placeholder="Ej: Resina, Pintura negra, Fibra..."
                    class="input-field">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Unidad de medida *</label>
                <div class="grid grid-cols-3 gap-2" x-data="{ unit: '{{ old('unit', $material->unit ?? '') }}' }">
                    <input type="hidden" name="unit" :value="unit">
                    @foreach(['kg' => 'Kilogramos', 'g' => 'Gramos', 'lt' => 'Litros', 'ml' => 'Mililitros', 'unidad' => 'Unidades'] as $val => $label)
                    <button type="button"
                        @click="unit = '{{ $val }}'"
                        :class="unit === '{{ $val }}' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600'"
                        class="border-2 rounded-xl py-2 text-sm font-semibold transition-all">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Proveedor habitual</label>
                <select name="supplier_id" class="input-field">
                    <option value="">Sin proveedor asignado</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}"
                        {{ old('supplier_id', $material->supplier_id) == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            @if($material->exists)
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="active" value="1"
                    {{ old('active', $material->active) ? 'checked' : '' }}
                    class="w-5 h-5 text-blue-600 rounded">
                <span class="text-base font-semibold text-gray-700">Material activo</span>
            </label>
            @endif
        </div>

        <button type="submit" class="btn-primary">
            {{ $material->exists ? 'Guardar cambios' : 'Crear material' }}
        </button>
    </form>
</div>
</x-app-layout>