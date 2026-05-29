<x-app-layout title="Etapas">
    <div class="flex justify-between items-center mt-4 mb-3">
        <h1 class="text-lg font-bold text-gray-800">Etapas de producción</h1>
        <a href="/stages/create"
            class="bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">+ Nueva</a>
    </div>

    <div class="space-y-3">
        @forelse($stages as $stage)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $stage->color }}"></div>
                    <div>
                        <div class="font-semibold text-gray-800">{{ $stage->name }}</div>
                        <div class="text-xs text-gray-400">Orden: {{ $stage->order }}</div>
                    </div>
                    @if(!$stage->active)
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactiva</span>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="/stages/{{ $stage->id }}/edit"
                        class="text-xs bg-gray-100 px-3 py-1 rounded-lg text-gray-600">Editar</a>
                    <form method="POST" action="/stages/{{ $stage->id }}"
                        onsubmit="return confirm('¿Eliminar esta etapa?')">
                        @csrf @method('DELETE')
                        <button class="text-xs bg-red-50 px-3 py-1 rounded-lg text-red-600">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-gray-400 py-12">
            <div class="text-4xl mb-2">⚙️</div>
            <div>No hay etapas configuradas</div>
        </div>
        @endforelse
    </div>
</x-app-layout>