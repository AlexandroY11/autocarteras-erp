<x-app-layout title="Clientes">
<div class="pt-4 space-y-4">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Clientes</h1>
            <p class="text-sm text-gray-500">{{ $total }} clientes en total</p>
        </div>
        <a href="/clients/create"
            class="flex items-center gap-2 bg-blue-700 text-white font-bold px-4 py-2.5 rounded-2xl text-sm active:scale-95 transition-transform">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nuevo
        </a>
    </div>

    {{-- Top departamentos --}}
    @if($byDepartment->count() > 0)
    <div class="bg-white card p-4">
        <p class="section-title mb-3">Por departamento</p>
        <div class="space-y-2">
            @foreach($byDepartment as $row)
            <a href="/clients?department_id={{ $row->department_id }}"
                class="flex items-center gap-3 {{ request('department_id') == $row->department_id ? 'opacity-100' : 'opacity-80' }}">
                <div class="flex-1">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-semibold text-gray-700">
                            {{ $row->department?->name ?? 'Sin departamento' }}
                        </span>
                        <span class="text-sm font-black text-gray-900">{{ $row->total }}</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full"
                            style="width: {{ $byDepartment->max('total') > 0 ? ($row->total / $byDepartment->max('total') * 100) : 0 }}%">
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filtros --}}
    <form method="GET" action="/clients" class="space-y-2" x-data="{
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

        {{-- Búsqueda --}}
        <div class="relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Buscar por nombre o teléfono..."
                class="input-field pl-12">
        </div>

        {{-- Departamento + Ciudad --}}
        <div class="grid grid-cols-2 gap-2">
            <select name="department_id"
                x-model="departmentId"
                @change="loadCities($event.target.value)"
                class="input-field text-sm py-3">
                <option value="">Todos los departamentos</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
                @endforeach
            </select>

            <select name="city_id" :disabled="!departmentId" class="input-field text-sm py-3 disabled:bg-gray-100">
                <option value="">Todas las ciudades</option>
                @if(request('city_id') && request('department_id'))
                    @foreach($cities as $city)
                    <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                    @endforeach
                @endif
                <template x-for="city in cities" :key="city.id">
                    <option :value="city.id" x-text="city.name"
                        :selected="city.id == {{ request('city_id', 0) }}">
                    </option>
                </template>
            </select>
        </div>

        <button type="submit" class="btn-primary py-3 text-sm">
            Filtrar
        </button>

        @if(request()->hasAny(['search', 'department_id', 'city_id']))
        <a href="/clients" class="block text-center text-sm text-gray-500 underline">
            Limpiar filtros
        </a>
        @endif
    </form>

    {{-- Lista --}}
    <div class="space-y-3">
        @forelse($clients as $client)
        <div class="bg-white card p-4">
            <div class="flex justify-between items-start gap-3">
                <div class="flex-1 min-w-0">

                    {{-- Nombre --}}
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
                            <span class="text-blue-700 font-black text-sm">
                                {{ strtoupper(substr($client->first_name, 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-base">{{ $client->full_name }}</div>
                            @if(!$client->active)
                            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold">Inactivo</span>
                            @endif
                        </div>
                    </div>

                    {{-- Datos --}}
                    <div class="space-y-1 ml-11">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                            </svg>
                            <span>{{ $client->phone }}</span>
                        </div>

                        @if($client->address)
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                            </svg>
                            <span class="truncate">{{ $client->address }}</span>
                        </div>
                        @endif

                        @if($client->city || $client->department)
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                            <span>
                                {{ $client->city?->name }}
                                @if($client->city && $client->department), @endif
                                {{ $client->department?->name }}
                            </span>
                        </div>
                        @endif

                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                            </svg>
                            Cliente desde {{ $client->created_at->format('d/m/Y') }}
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex flex-col gap-2 shrink-0">
                    <a href="/clients/{{ $client->id }}/edit"
                        class="flex items-center gap-1.5 text-xs bg-gray-100 px-3 py-2 rounded-xl text-gray-600 font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                        </svg>
                        Editar
                    </a>
                    <form method="POST" action="/clients/{{ $client->id }}"
                        onsubmit="return confirm('¿Eliminar este cliente?')">
                        @csrf @method('DELETE')
                        <button class="flex items-center gap-1.5 text-xs bg-red-50 px-3 py-2 rounded-xl text-red-600 font-semibold w-full">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                            </svg>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <svg class="w-16 h-16 text-gray-200 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
            </svg>
            <div class="text-xl font-bold text-gray-300">Sin clientes</div>
        </div>
        @endforelse
    </div>

    <div class="mt-2">{{ $clients->links() }}</div>
</div>
</x-app-layout>