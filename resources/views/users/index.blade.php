<x-app-layout title="Usuarios">
    <div class="flex justify-between items-center mt-4 mb-3">
        <h1 class="text-lg font-bold text-gray-800">Usuarios</h1>
        <a href="/users/create"
            class="bg-blue-700 text-white text-sm px-4 py-2 rounded-lg">+ Nuevo</a>
    </div>

    <div class="space-y-3">
        @forelse($users as $user)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm
                        {{ $user->isAdmin() ? 'bg-blue-600' : 'bg-gray-400' }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 flex items-center gap-2">
                            {{ $user->name }}
                            @if(!$user->active)
                            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Inactivo</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                        @if($user->phone)
                        <div class="text-xs text-gray-400">📱 {{ $user->phone }}</div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $user->isAdmin() ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $user->isAdmin() ? 'Admin' : 'Trabajador' }}
                    </span>
                    <div class="flex gap-2">
                        <a href="/users/{{ $user->id }}/edit"
                            class="text-xs bg-gray-100 px-3 py-1 rounded-lg text-gray-600">Editar</a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="/users/{{ $user->id }}"
                            onsubmit="return confirm('¿Eliminar este usuario?')">
                            @csrf @method('DELETE')
                            <button class="text-xs bg-red-50 px-3 py-1 rounded-lg text-red-600">Eliminar</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-gray-400 py-12">
            <div class="text-4xl mb-2">👥</div>
            <div>No hay usuarios</div>
        </div>
        @endforelse
    </div>
</x-app-layout>