<x-app-layout title="Etapas">
<div class="pt-4 space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Flujo de Producción</h1>
            <p class="text-sm font-medium text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded-lg mt-1">
                Configuración del Roadmap
            </p>
        </div>
        <a href="/stages/create"
            class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm active:scale-95 transition-all shadow-md shadow-blue-100">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nueva Etapa
        </a>
    </div>

    {{-- 2. ROADMAP / TIMELINE --}}
    <div class="relative">
        {{-- Línea vertical conectora --}}
        <div class="absolute left-8 top-0 bottom-0 w-1 bg-gray-100 rounded-full hidden md:block"></div>

        <div class="space-y-4 relative">
            @forelse($stages as $stage)
            <div class="group relative flex flex-col md:flex-row md:items-center gap-4">
                
                {{-- Indicador de Orden (Círculo en el Roadmap) --}}
                <div class="hidden md:flex shrink-0 w-16 h-16 rounded-2xl bg-white border-4 border-gray-50 shadow-sm z-10 items-center justify-center transition-all group-hover:border-blue-100 group-hover:scale-110">
                    <span class="text-xl font-black text-gray-300 group-hover:text-blue-600 transition-colors">
                        {{ str_pad($stage->order, 2, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                {{-- Card de la Etapa --}}
                <div class="flex-1 bg-white rounded-[2rem] p-5 border border-gray-100 shadow-sm hover:shadow-md transition-all relative overflow-hidden">
                    
                    {{-- Color de la etapa (indicador lateral) --}}
                    <div class="absolute left-0 top-0 bottom-0 w-2" style="background-color: {{ $stage->color }}"></div>

                    <div class="flex items-center justify-between gap-4 ml-2">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: {{ $stage->color }}20">
                                <svg class="w-6 h-6" style="color: {{ $stage->color }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg leading-tight">{{ $stage->name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Etapa {{ $stage->order }}</span>
                                    @if(!$stage->active)
                                        <span class="bg-gray-100 text-gray-500 text-[9px] font-black uppercase px-2 py-0.5 rounded-lg">Inactiva</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="flex gap-2">
                            <a href="/stages/{{ $stage->id }}/edit" 
                               class="p-2.5 bg-gray-50 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all shadow-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            <form id="delete-form-{{ $stage->id }}" method="POST" action="/stages/{{ $stage->id }}">
                                @csrf @method('DELETE')
                                <button type="button" 
                                    @click="showAlert.delete('delete-form-{{ $stage->id }}', '¿Deseas eliminar la etapa {{ $stage->name }}?')"
                                    class="p-2.5 bg-gray-50 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all shadow-sm">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                <h3 class="text-xl font-bold text-gray-300">No hay etapas configuradas</h3>
            </div>
            @endforelse
        </div>
    </div>
</div>
</x-app-layout>
