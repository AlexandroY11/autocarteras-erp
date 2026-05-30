<x-app-layout title="{{ $supplier->exists ? 'Editar Proveedor' : 'Nuevo Proveedor' }}">
<div class="pt-4">
    <div class="flex items-center gap-3 mb-6">
        <a href="/suppliers" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-black text-gray-900">
            {{ $supplier->exists ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
        </h1>
    </div>

    <form method="POST"
        action="{{ $supplier->exists ? '/suppliers/'.$supplier->id : '/suppliers' }}"
        class="space-y-4">
        @csrf
        @if($supplier->exists) @method('PUT') @endif

        <div class="bg-white card p-4 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre de la empresa *</label>
                <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                    class="input-field">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                    class="input-field">
            </div>
            @if($supplier->exists)
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="active" value="1"
                    {{ old('active', $supplier->active) ? 'checked' : '' }}
                    class="w-5 h-5 text-blue-600 rounded">
                <span class="text-base font-semibold text-gray-700">Proveedor activo</span>
            </label>
            @endif
        </div>

        <button type="submit" class="btn-primary">
            {{ $supplier->exists ? 'Guardar cambios' : 'Crear proveedor' }}
        </button>
    </form>
</div>
</x-app-layout>