<x-app-layout title="{{ $user->exists ? 'Editar Usuario' : 'Nuevo Usuario' }}">
<div class="pt-4">
    <div class="flex items-center gap-3 mb-6">
        <a href="/users" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-900">{{ $user->exists ? 'Editar Usuario' : 'Nuevo Usuario' }}</h1>
        </div>
    </div>

    <form method="POST"
        action="{{ $user->exists ? '/users/'.$user->id : '/users' }}"
        class="space-y-4"
        x-data="{ role: '{{ old('role', $user->role ?? 'worker') }}' }">
        @csrf
        @if($user->exists) @method('PUT') @endif

        {{-- Info básica --}}
        <div class="bg-white card p-4 space-y-4">
            <p class="section-title">Información</p>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre completo *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                    class="input-field">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Correo *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="input-field">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                    class="input-field">
            </div>
        </div>

        {{-- Rol --}}
        <div class="bg-white card p-4 space-y-3">
            <p class="section-title">Rol</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['admin' => ['🔑', 'Admin'], 'director' => ['👷', 'Director'], 'worker' => ['🔧', 'Lijador']] as $r => [$icon, $label])
                <label class="flex flex-col items-center gap-1 border-2 rounded-2xl p-3 cursor-pointer transition-all"
                    :class="role === '{{ $r }}' ? 'border-blue-600 bg-blue-50' : 'border-gray-200'">
                    <input type="radio" name="role" value="{{ $r }}" x-model="role" class="hidden">
                    <span class="text-2xl">{{ $icon }}</span>
                    <span class="text-xs font-bold text-gray-700">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Skills --}}
        <div class="bg-white card p-4 space-y-3" x-show="role !== 'admin'">
            <p class="section-title">Etapas que puede avanzar</p>
            <p class="text-xs text-gray-500">Selecciona las etapas que este trabajador puede realizar</p>
            <div class="space-y-2">
                @foreach($stages as $stage)
                <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition-all"
                    :class="'border-gray-200'">
                    <input type="checkbox" name="skills[]" value="{{ $stage->id }}"
                        {{ in_array($stage->id, old('skills', $user->exists ? $user->skills->pluck('id')->toArray() : [])) ? 'checked' : '' }}
                        class="w-5 h-5 text-blue-600 rounded">
                    <div class="w-3 h-3 rounded-full" style="background: {{ $stage->color }}"></div>
                    <span class="text-base font-semibold text-gray-700">{{ $stage->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Contraseña --}}
        <div class="bg-white card p-4 space-y-4">
            <p class="section-title">{{ $user->exists ? 'Cambiar contraseña' : 'Contraseña' }}</p>
            <div>
                <input type="password" name="password"
                    placeholder="{{ $user->exists ? 'Dejar vacío para no cambiar' : 'Contraseña *' }}"
                    {{ $user->exists ? '' : 'required' }}
                    class="input-field">
            </div>
            <div>
                <input type="password" name="password_confirmation"
                    placeholder="Confirmar contraseña"
                    class="input-field">
            </div>
        </div>

        @if($user->exists)
        <div class="bg-white card p-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="active" value="1"
                    {{ old('active', $user->active) ? 'checked' : '' }}
                    class="w-5 h-5 text-blue-600 rounded">
                <span class="text-base font-semibold text-gray-700">Usuario activo</span>
            </label>
        </div>
        @endif

        <button type="submit" class="btn-primary">
            {{ $user->exists ? 'Guardar cambios' : 'Crear usuario' }}
        </button>
    </form>
</div>
</x-app-layout>