<x-app-layout title="Clientes">
    <div class="flex justify-between items-center mt-4 mb-3">
        <h1 class="text-lg font-bold text-gray-800">Clientes</h1>
        <a href="/clients/create"
            class="bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">+ Nuevo</a>
    </div>

    <form method="GET" action="/clients" class="mb-3">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Buscar por nombre o teléfono..."
            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </form>

    <div class="space-y-3">
        @forelse($clients as $client)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-semibold text-gray-800">{{ $client->full_name }}</div>
                    <div class="text-sm text-gray-500">📱 {{ $client->phone }}</div>
                    @if($client->city)
                    <div class="text-sm text-gray-400">📍 {{ $client->city }}, {{ $client->department }}</div>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="/clients/{{ $client->id }}/edit"
                        class="text-xs bg-gray-100 px-3 py-1 rounded-lg text-gray-600">Editar</a>
                    <form method="POST" action="/clients/{{ $client->id }}"
                        onsubmit="return confirm('¿Eliminar este cliente?')">
                        @csrf @method('DELETE')
                        <button class="text-xs bg-red-50 px-3 py-1 rounded-lg text-red-600">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-gray-400 py-12">
            <div class="text-4xl mb-2">👤</div>
            <div>No hay clientes</div>
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
</x-app-layout>