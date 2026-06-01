<x-app-layout title="Equipo">
<div class="pt-4 space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Equipo</h1>
            <p class="text-sm font-medium text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded-lg mt-1">
                Gestión de personal y accesos
            </p>
        </div>
        <a href="/users/create"
            class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm active:scale-95 transition-all shadow-md shadow-blue-100">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nuevo Usuario
        </a>
    </div>

    {{-- 2. LISTADO DE USUARIOS (CARDS) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($users as $user)
        <div class="bg-white rounded-[2.5rem] p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
            
            {{-- Decoración sutil según rol --}}
            <div class="absolute top-0 right-0 w-32 h-32 {{ $user->isAdmin() ? 'bg-blue-50' : 'bg-gray-50' }} rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>

            <div class="flex items-start gap-4 relative">
                {{-- Avatar con inicial --}}
                <div class="w-16 h-16 {{ $user->isAdmin() ? 'bg-blue-600 shadow-blue-100' : 'bg-gray-400 shadow-gray-100' }} rounded-2xl flex items-center justify-center shrink-0 shadow-lg transition-transform group-hover:scale-105">
                    <span class="text-white font-black text-2xl">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <div class="min-w-0">
                            <h3 class="font-bold text-gray-900 text-lg leading-tight truncate flex items-center gap-2">
                                {{ $user->name }}
                                @if(!$user->active)
                                    <span class="bg-red-50 text-red-600 text-[9px] font-black uppercase px-2 py-0.5 rounded-lg">Inactivo</span>
                                @endif
                            </h3>
                            <p class="text-blue-600 font-bold text-sm truncate">{{ $user->email }}</p>
                        </div>
                    </div>

                    {{-- Detalles --}}
                    <div class="mt-4 flex flex-wrap items-center gap-4">
                        {{-- Rol --}}
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 {{ $user->isAdmin() ? 'bg-blue-50' : 'bg-gray-50' }} rounded-lg">
                                <svg class="w-3.5 h-3.5 {{ $user->isAdmin() ? 'text-blue-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-black {{ $user->isAdmin() ? 'text-blue-700' : 'text-gray-500' }} uppercase tracking-widest">
                                {{ $user->isAdmin() ? 'Administrador' : 'Trabajador' }}
                            </span>
                        </div>

                        {{-- Teléfono --}}
                        @if($user->phone)
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-gray-50 rounded-lg">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ $user->phone }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="mt-6 pt-4 border-t border-gray-50 flex justify-end gap-2">
                <a href="/users/{{ $user->id }}/edit" 
                   class="p-2.5 bg-gray-50 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                    </svg>
                </a>
                
                @if($user->id !== auth()->id())
                <form id="delete-form-{{ $user->id }}" method="POST" action="/users/{{ $user->id }}">
                    @csrf @method('DELETE')
                    <button type="button" 
                        @click="confirmDelete('delete-form-{{ $user->id }}', '¿Deseas eliminar al usuario {{ $user->name }}?')"
                        class="p-2.5 bg-gray-50 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-20 bg-gray-50 rounded-[3rem] border-2 border-dashed border-gray-200">
            <h3 class="text-xl font-bold text-gray-400">No hay usuarios registrados</h3>
        </div>
        @endforelse
    </div>
</div>
</x-app-layout>
