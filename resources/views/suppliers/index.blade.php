<x-app-layout title="Proveedores">
<div class="pt-4">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Proveedores</h1>
            <p class="text-sm text-gray-500">{{ $suppliers->count() }} proveedores</p>
        </div>
        <a href="/suppliers/create" class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm">
            + Nuevo
        </a>
    </div>

    <div class="space-y-3">
        @forelse($suppliers as $supplier)
        <div class="bg-white card p-4 flex justify-between items-center">
            <div>
                <div class="text-lg font-bold text-gray-900">{{ $supplier->name }}</div>
                @if($supplier->phone)
                <div class="text-sm text-gray-500">📱 {{ $supplier->phone }}</div>
                @endif
                @if(!$supplier->active)
                <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Inactivo</span>
                @endif
            </div>
            <div class="flex gap-2">
                <a href="/suppliers/{{ $supplier->id }}/edit"
                    class="text-sm bg-gray-100 px-3 py-2 rounded-xl text-gray-600 font-semibold">Editar</a>
                <form method="POST" action="/suppliers/{{ $supplier->id }}"
                    onsubmit="return confirm('¿Eliminar?')">
                    @csrf @method('DELETE')
                    <button class="text-sm bg-red-50 px-3 py-2 rounded-xl text-red-600 font-semibold">Eliminar</button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <div class="text-6xl mb-4">🏭</div>
            <div class="text-xl font-bold text-gray-400">Sin proveedores</div>
        </div>
        @endforelse
    </div>
</div>
</x-app-layout>