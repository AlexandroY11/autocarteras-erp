<x-app-layout title="Mi Perfil">
<div class="max-w-2xl mx-auto pt-4 pb-12" x-data="{ registering: false }">
    
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Mi Perfil</h1>
        <p class="text-sm font-medium text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded-lg mt-1">
            Configuración de seguridad
        </p>
    </div>

    <div class="space-y-6">
        {{-- Información Básica --}}
        <div class="bg-white rounded-[2.5rem] p-8 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-6 mb-8">
                <div class="w-20 h-20 bg-blue-600 rounded-[2rem] flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-blue-200">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-900">{{ auth()->user()->name }}</h2>
                    <p class="text-gray-500 font-medium">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl">
                    <span class="text-sm font-bold text-gray-500 uppercase tracking-wider">Rol de acceso</span>
                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-xl text-xs font-black uppercase">
                        {{ auth()->user()->role }}
                    </span>
                </div>
            </div>
        </div>

        {{-- SECCIÓN DE HUELLA / PASSKEYS --}}
        <div class="bg-gray-900 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-blue-200 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-blue-500/10 rounded-full -mr-20 -mt-20"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-blue-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path d="M7.875 14.25l1.214 1.942a2.25 2.25 0 001.908 1.058h2.006c.776 0 1.497-.4 1.908-1.058l1.214-1.942M2.41 9h4.636a2.25 2.25 0 011.872 1.002l.164.246a2.25 2.25 0 001.872 1.002h2.292a2.25 2.25 0 001.872-1.002l.164-.246A2.25 2.25 0 0116.954 9h4.636M1.5 9.75a6.75 6.75 0 1113.5 0v2.25a6.75 6.75 0 11-13.5 0v-2.25z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black">Acceso con Huella</h3>
                </div>
                
                <p class="text-gray-400 text-sm mb-8 leading-relaxed">
                    Activa el inicio de sesión con tu huella digital o FaceID. Es más seguro y no necesitarás recordar tu contraseña.
                </p>

                <form method="GET" action="{{ route('webauthn.create') }}" @submit="registering = true">
                    @csrf
                    <button type="submit" :disabled="registering"
                        class="w-full bg-blue-600 hover:bg-blue-500 disabled:bg-gray-700 text-white font-black py-4 rounded-2xl transition-all flex items-center justify-center gap-3 group">
                        <template x-if="!registering">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Activar mi Huella / FaceID
                            </span>
                        </template>
                        <template x-if="registering">
                            <span class="flex items-center gap-3">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Comunicando con el dispositivo...
                            </span>
                        </template>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
