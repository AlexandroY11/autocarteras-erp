<x-app-layout title="{{ $client->exists ? 'Editar Cliente' : 'Nuevo Cliente' }}">
<div class="pt-4">
    <div class="flex items-center gap-3 mb-6">
        <a href="/clients" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-black text-gray-900">
            {{ $client->exists ? 'Editar Cliente' : 'Nuevo Cliente' }}
        </h1>
    </div>

    <form method="POST"
        action="{{ $client->exists ? '/clients/'.$client->id : '/clients' }}"
        class="space-y-4"
        x-data="{
            departmentId: '{{ old('department_id', $client->department_id ?? '') }}',
            cityId: '{{ old('city_id', $client->city_id ?? '') }}',
            cities: [],
            loadingCities: false,

            async loadCities(deptId, preselectId) {
                if (!deptId) { this.cities = []; return; }
                this.loadingCities = true;
                const res = await fetch('/api/cities/' + deptId);
                this.cities = await res.json();
                this.loadingCities = false;
                if (preselectId) this.cityId = String(preselectId);
            },

            async init() {
                if (this.departmentId) {
                    await this.loadCities(this.departmentId, this.cityId);
                }
            }
        }"
        x-init="init()">
        @csrf
        @if($client->exists) @method('PUT') @endif

        {{-- Datos personales --}}
        <div class="bg-white card p-4 space-y-4">
            <p class="section-title">Información personal</p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre *</label>
                    <input type="text" name="first_name"
                        value="{{ old('first_name', $client->first_name) }}" required
                        class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Apellido *</label>
                    <input type="text" name="last_name"
                        value="{{ old('last_name', $client->last_name) }}" required
                        class="input-field">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono / WhatsApp *</label>
                <input type="text" name="phone"
                    value="{{ old('phone', $client->phone) }}" required
                    class="input-field">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Correo</label>
                <input type="email" name="email"
                    value="{{ old('email', $client->email) }}"
                    class="input-field">
            </div>
        </div>

        {{-- Ubicación --}}
        <div class="bg-white card p-4 space-y-4">
            <p class="section-title">Ubicación</p>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Dirección</label>
                <input type="text" name="address"
                    value="{{ old('address', $client->address) }}"
                    placeholder="Ej: Cra 5 # 12-34"
                    class="input-field">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Departamento</label>
                <select name="department_id"
                    x-model="departmentId"
                    @change="loadCities($event.target.value, null); cityId = ''"
                    class="input-field">
                    <option value="">Seleccionar departamento...</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}"
                        {{ old('department_id', $client->department_id) == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Ciudad</label>
                <select name="city_id"
                    x-model="cityId"
                    :disabled="!departmentId || loadingCities"
                    class="input-field disabled:bg-gray-100 disabled:text-gray-400">
                    <option value="">
                        <span x-text="loadingCities ? 'Cargando...' : (departmentId ? 'Seleccionar ciudad...' : 'Primero selecciona departamento')"></span>
                    </option>
                    <template x-for="city in cities" :key="city.id">
                        <option :value="String(city.id)"
                            :selected="String(city.id) === String(cityId)"
                            x-text="city.name">
                        </option>
                    </template>
                </select>
            </div>
        </div>

        @if($client->exists)
        <div class="bg-white card p-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="active" value="1"
                    {{ old('active', $client->active) ? 'checked' : '' }}
                    class="w-5 h-5 text-blue-600 rounded">
                <span class="text-base font-semibold text-gray-700">Cliente activo</span>
            </label>
        </div>
        @endif

        <button type="submit" class="btn-primary">
            {{ $client->exists ? 'Guardar cambios' : 'Crear cliente' }}
        </button>
    </form>
</div>
</x-app-layout>