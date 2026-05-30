<x-app-layout title="{{ $user->exists ? 'Editar Usuario' : 'Nuevo Usuario' }}">
    <div class="flex items-center gap-3 mt-4 mb-4">
        <a href="/users" class="text-gray-500">← Volver</a>
        <h1 class="text-lg font-bold text-gray-800">
            {{ $user->exists ? 'Editar Usuario' : 'Nuevo Usuario' }}
        </h1>
    </div>

    <form method="POST"
        action="{{ $user->exists ? '/users/'.$user->id : '/users' }}"
        class="space-y-4 bg-white rounded-xl p-4 shadow-sm">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo *</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
            <select name="role"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="worker" {{ old('role', $user->role) === 'worker' ? 'selected' : '' }}>
                    Trabajador
                </option>
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                    Administrador
                </option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ $user->exists ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña *' }}
            </label>
            <input type="password" name="password"
                {{ $user->exists ? '' : 'required' }}
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
            <input type="password" name="password_confirmation"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        @if($user->exists)
        <div class="flex items-center gap-2">
            <input type="checkbox" name="active" id="active" value="1"
                {{ old('active', $user->active) ? 'checked' : '' }}
                class="w-4 h-4 text-blue-600">
            <label for="active" class="text-sm text-gray-700">Usuario activo</label>
        </div>
        @endif

        <button type="submit"
            class="w-full bg-blue-700 text-white font-semibold py-3 rounded-xl">
            {{ $user->exists ? 'Guardar cambios' : 'Crear usuario' }}
        </button>
    </form>
</x-app-layout>