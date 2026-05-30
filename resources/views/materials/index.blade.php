<x-app-layout title="Materiales">
<div class="pt-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Materiales</h1>
            <p class="text-sm text-gray-500">{{ $materials->count() }} materiales</p>
        </div>
        <a href="/materials/create" class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm">
            + Nuevo
        </a>
    </div>

    <div class="space-y-3">
        @forelse($materials as $material)
        <div class="bg-white card p-4 flex justify-between items-center">
            <div>
                <div class="text-lg font-bold text-gray-900">{{ $material->name }}</div>
                <div class="text-sm text-gray-500">
                    {{ $material->unit_label }}
                    @if($material->supplier)
                     · {{ $material->supplier->name }}
                    @endif
                </div>
                @if(!$material->active)
                <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Inactivo</span>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="/materials/{{ $material->id }}/edit"
                    class="text-sm bg-gray-100 px-3 py-2 rounded-xl text-gray-600 font-semibold">Editar</a>
                <form method="POST" action="/materials/{{ $material->id }}"
                    onsubmit="return confirm('¿Eliminar?')">
                    @csrf @method('DELETE')
                    <button class="text-sm bg-red-50 px-3 py-2 rounded-xl text-red-600 font-semibold">Eliminar</button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <div class="text-6xl mb-4">🧴</div>
            <div class="text-xl font-bold text-gray-400">Sin materiales</div>
        </div>
        @endforelse
    </div>
</div>
</x-app-layout>