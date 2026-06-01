<x-app-layout title="Clientes">
<div class="pt-4 space-y-5" x-data="{ showFilters: false }">

    {{-- 1. HEADER & RESUMEN --}}
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Clientes</h1>
            <p class="text-sm font-medium text-blue-600 bg-blue-50 inline-block px-2 py-0.5 rounded-lg mt-1">
                {{ $total }} registros encontrados
            </p>
        </div>
        <div class="flex gap-2">
            <button @click="showFilters = !showFilters" 
                class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 font-bold px-4 py-2.5 rounded-2xl text-sm active:scale-95 transition-all shadow-sm">
                <svg class="w-4 h-4" :class="showFilters ? 'text-blue-600' : 'text-gray-400'" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                Filtros
                <span x-show="{{ request()->hasAny(['search', 'department_id', 'city_id']) ? 'true' : 'false' }}" class="w-2 h-2 bg-blue-600 rounded-full"></span>
            </button>
            <a href="/clients/create"
                class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm active:scale-95 transition-all shadow-md shadow-blue-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nuevo
            </a>
        </div>
    </div>

    {{-- 2. PANEL DE FILTROS (Colapsable) --}}
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-gray-50 border border-gray-200 rounded-3xl p-5 shadow-inner">
        
        <form method="GET" action="/clients" class="space-y-4" x-data="{
            departmentId: '{{ request('department_id') }}',
            cities: [],
            async loadCities(id) {
                if (!id) { this.cities = []; return; }
                const res = await fetch('/api/cities/' + id);
                this.cities = await res.json();
            },
            async init() {
                if (this.departmentId) await this.loadCities(this.departmentId);
            }
        }" x-init="init()">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Búsqueda --}}
                <div class="md:col-span-3 relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Buscar por nombre, apellido o teléfono..."
                        class="w-full border-none rounded-2xl px-5 py-3.5 pl-12 text-sm focus:ring-2 focus:ring-blue-500 shadow-sm">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </div>

                {{-- Departamento --}}
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Departamento</label>
                    <select name="department_id" x-model="departmentId" @change="loadCities($event.target.value)"
                        class="w-full border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 shadow-sm">
                        <option value="">Todos</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Ciudad --}}
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Ciudad</label>
                    <select name="city_id" :disabled="!departmentId" 
                        class="w-full border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 shadow-sm disabled:opacity-50">
                        <option value="">Todas</option>
                        <template x-for="city in cities" :key="city.id">
                            <option :value="city.id" x-text="city.name" :selected="city.id == {{ request('city_id', 0) }}"></option>
                        </template>
                    </select>
                </div>

                {{-- Botones Acción --}}
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-blue-700 text-white font-bold py-3 rounded-2xl text-sm hover:bg-blue-800 transition-colors shadow-lg shadow-blue-100">
                        Aplicar Filtros
                    </button>
                    @if(request()->hasAny(['search', 'department_id', 'city_id']))
                    <a href="/clients" class="bg-white border border-gray-200 text-gray-500 p-3 rounded-2xl hover:bg-gray-100 transition-colors shadow-sm" title="Limpiar">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- 3. ESTADÍSTICAS RÁPIDAS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach($byDepartment->take(4) as $row)
        <div class="bg-white p-4 rounded-3xl border border-gray-100 shadow-sm">
            <p class="text-[10px] font-bold text-gray-400 uppercase truncate">{{ $row->department?->name ?? 'Sin Dept.' }}</p>
            <div class="flex items-baseline gap-1">
                <span class="text-2xl font-black text-gray-900">{{ $row->total }}</span>
                <span class="text-[10px] text-gray-500 font-medium">clientes</span>
            </div>
            <div class="w-full bg-gray-100 h-1 rounded-full mt-2 overflow-hidden">
                <div class="bg-blue-500 h-full" style="width: {{ ($row->total / $total) * 100 }}%"></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 4. LISTADO DE CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($clients as $client)
        <div class="bg-white rounded-[2.5rem] p-6 border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
            
            {{-- Decoración de fondo --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>

            <div class="relative">
                {{-- A. ENCABEZADO: Avatar y Nombre (Lado a lado) --}}
                <div class="flex items-center gap-4 mb-6">
                    @php
                        $colors = ['bg-blue-500', 'bg-purple-500', 'bg-orange-500', 'bg-emerald-500', 'bg-pink-500'];
                        $color = $colors[$client->id % count($colors)];
                    @endphp
                    <div class="w-16 h-16 {{ $color }} rounded-2xl flex items-center justify-center shrink-0 shadow-lg shadow-{{ str_replace('bg-', '', $color) }}/20">
                        <span class="text-white font-black text-2xl">
                            {{ strtoupper(substr($client->first_name, 0, 1)) }}
                        </span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-gray-900 text-xl leading-tight truncate">{{ $client->full_name }}</h3>
                                <a href="https://wa.me/57{{ preg_replace('/[^0-9]/', '', $client->phone ) }}" target="_blank" class="inline-block mt-1">
                                    <p class="text-blue-600 font-bold text-base hover:underline">{{ $client->phone }}</p>
                                </a>
                            </div>
                            @if(!$client->active)
                                <span class="bg-red-50 text-red-600 text-[10px] font-black uppercase px-2 py-1 rounded-lg shrink-0">Inactivo</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- B. CUERPO: Información (Ocupa todo el ancho debajo del avatar) --}}
                <div class="space-y-4">
                    {{-- Dirección --}}
                    <div class="flex items-start gap-3 text-gray-600 bg-gray-50/50 p-4 rounded-3xl border border-gray-50">
                        <div class="p-2 bg-white rounded-xl shrink-0 shadow-sm">
                            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Dirección de entrega</p>
                            <p class="text-sm font-bold text-gray-800 leading-snug">
                                {{ $client->address ?? 'Sin dirección registrada' }}
                            </p>
                        </div>
                    </div>

                    {{-- Grid inferior para Zona y Registro --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-3 px-1">
                            <div class="p-2 bg-gray-50 rounded-xl shrink-0">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Zona</p>
                                <p class="text-xs font-bold text-gray-700 truncate">
                                    {{ $client->city?->name ?? 'N/A' }}, {{ $client->department?->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 px-1">
                            <div class="p-2 bg-gray-50 rounded-xl shrink-0">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Registro</p>
                                <p class="text-xs font-bold text-gray-700 truncate">{{ $client->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- C. ACCIONES: (Al final y alineadas a la derecha) --}}
                <div class="mt-6 pt-4 border-t border-gray-50 flex justify-end gap-3">
                    <a href="/clients/{{ $client->id }}/edit" class="flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-2xl transition-all font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                        </svg>
                        Editar
                    </a>
                    <form id="delete-form-{{ $client->id }}" method="POST" action="/clients/{{ $client->id }}">
                        @csrf @method('DELETE')
                        <button type="button" 
                            @click="confirmDelete('delete-form-{{ $client->id }}', 'El cliente {{ $client->full_name }} será eliminado.')"
                            class="flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-2xl transition-all font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-20 bg-gray-50 rounded-[3rem] border-2 border-dashed border-gray-200">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-400">No hay clientes que coincidan</h3>
            <p class="text-gray-400 text-sm">Prueba ajustando los filtros o la búsqueda.</p>
        </div>
        @endforelse
    </div>

    {{-- 5. PAGINACIÓN --}}
    <div class="mt-4">{{ $clients->links() }}</div>
</div>
</x-app-layout>
