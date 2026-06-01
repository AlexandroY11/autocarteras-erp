@php
    $isEdit = $user->exists;
    $title = $isEdit ? 'Editar Usuario' : 'Crear Usuario';
    $route = $isEdit ? "/users/{$user->id}" : "/users";
    $buttonText = $isEdit ? 'Actualizar Usuario' : 'Guardar Usuario';
@endphp

<x-app-layout :title="$title">
<div class="max-w-2xl mx-auto pt-4 pb-12">
    
    {{-- Header con botón volver --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="/users" class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-blue-600 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ $title }}</h1>
            <p class="text-sm text-gray-500 font-medium">Gestiona el acceso y perfil del equipo</p>
        </div>
    </div>

    <form method="POST" action="{{ $route }}" class="space-y-6">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 relative overflow-hidden">
            {{-- Decoración sutil --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 rounded-full -mr-16 -mt-16"></div>

            <div class="space-y-6 relative">
                {{-- Nombre Completo --}}
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2 tracking-wider">Nombre Completo *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        placeholder="Ej: Juan Pérez"
                        class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Correo Electrónico --}}
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2 tracking-wider">Correo Electrónico *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            placeholder="juan@ejemplo.com"
                            class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
                    </div>

                    {{-- Teléfono --}}
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2 tracking-wider">Teléfono / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                            placeholder="300 000 0000"
                            class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
                    </div>
                </div>

                {{-- Contraseña --}}
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2 tracking-wider">
                        Contraseña {{ $isEdit ? '(Opcional)' : '*' }}
                    </label>
                    <input type="password" name="password" {{ $isEdit ? '' : 'required' }}
                        placeholder="{{ $isEdit ? 'Dejar en blanco para mantener actual' : 'Mínimo 8 caracteres' }}"
                        class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                    {{-- Rol --}}
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2 tracking-wider">Rol de Usuario</label>
                        <select name="role" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
                            <option value="worker" {{ old('role', $user->role) === 'worker' ? 'selected' : '' }}>Trabajador (Producción)</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador (Gestión)</option>
                        </select>
                    </div>

                    {{-- Estado Activo --}}
                    <div class="flex items-end pb-1">
                        <div class="w-full bg-gray-50 rounded-2xl p-4 border border-transparent hover:border-blue-100 transition-colors">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox" name="active" value="1" 
                                        {{ old('active', $user->active ?? true) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </div>
                                <span class="text-sm font-bold text-gray-700 group-hover:text-blue-700 transition-colors">Usuario Activo</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-blue-700 hover:bg-blue-800 text-white font-black py-5 rounded-[2rem] text-lg transition-all shadow-xl shadow-blue-100 active:scale-[0.98]">
            {{ $buttonText }}
        </button>
    </form>
</div>
</x-app-layout>