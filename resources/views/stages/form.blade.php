@php
    $isEdit = $stage->exists;
    $title = $isEdit ? 'Editar Etapa' : 'Crear Etapa';
    $route = $isEdit ? "/stages/{$stage->id}" : "/stages";
    $buttonText = $isEdit ? 'Actualizar Etapa' : 'Guardar Etapa';
@endphp

<x-app-layout :title="$title">
<div class="max-w-2xl mx-auto pt-4 pb-12">
    
    {{-- Header con botón volver --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="/stages" class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-blue-600 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ $title }}</h1>
            <p class="text-sm text-gray-500 font-medium">Define los pasos del flujo de producción</p>
        </div>
    </div>

    <form method="POST" action="{{ $route }}" class="space-y-6">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 relative overflow-hidden">
            {{-- Decoración sutil --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 rounded-full -mr-16 -mt-16"></div>

            <div class="space-y-6 relative">
                {{-- Nombre de la Etapa --}}
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2 tracking-wider">Nombre de la Etapa *</label>
                    <input type="text" name="name" value="{{ old('name', $stage->name) }}" required
                        placeholder="Ej: Corte, Costura, Empaque..."
                        class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Orden --}}
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2 tracking-wider">Orden en el Proceso *</label>
                        <input type="number" name="order" value="{{ old('order', $stage->order) }}" min="1" required
                            class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
                    </div>

                    {{-- Color --}}
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2 tracking-wider">Identificador Visual</label>
                        <div class="flex items-center gap-3 bg-gray-50 rounded-2xl px-4 py-2.5">
                            <input type="color" name="color" value="{{ old('color', $stage->color ?? '#3b82f6') }}"
                                class="w-10 h-10 border-none bg-transparent cursor-pointer rounded-lg overflow-hidden">
                            <span class="text-xs font-bold text-gray-500 uppercase">Elegir color</span>
                        </div>
                    </div>
                </div>

                {{-- Estado Activo (Solo en edición) --}}
                @if($isEdit)
                <div class="bg-gray-50 rounded-3xl p-5 border border-gray-100">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="active" id="active" value="1" 
                                {{ old('active', $stage->active) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-blue-700 transition-colors">Esta etapa se encuentra activa</span>
                    </label>
                </div>
                @endif
            </div>
        </div>

        <button type="submit"
            class="w-full bg-blue-700 hover:bg-blue-800 text-white font-black py-5 rounded-[2rem] text-lg transition-all shadow-xl shadow-blue-100 active:scale-[0.98]">
            {{ $buttonText }}
        </button>
    </form>
</div>
</x-app-layout>
