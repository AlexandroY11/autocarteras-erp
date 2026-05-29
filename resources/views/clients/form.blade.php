<x-app-layout title="{{ $client->exists ? 'Editar Cliente' : 'Nuevo Cliente' }}">
    <div class="flex items-center gap-3 mt-4 mb-4">
        <a href="/clients" class="text-gray-500">← Volver</a>
        <h1 class="text-lg font-bold text-gray-800">
            {{ $client->exists ? 'Editar Cliente' : 'Nuevo Cliente' }}
        </h1>
    </div>

    <form method="POST"
        action="{{ $client->exists ? '/clients/'.$client->id : '/clients' }}"
        class="space-y-4 bg-white rounded-xl p-4 shadow-sm"
        x-data="{
            departmentId: '{{ $client->exists ? optional(\App\Models\Department::where('name', $client->department)->first())->id : '' }}',
            cities: [],
            loadingCities: false,

            async loadCities(deptId) {
                if (!deptId) { this.cities = []; return; }
                this.loadingCities = true;
                const res = await fetch('/api/cities/' + deptId);
                this.cities = await res.json();
                this.loadingCities = false;
            },

            async init() {
                if (this.departmentId) {
                    await this.loadCities(this.departmentId);
                }
            }
        }"
        x-init="init()">
        @csrf
        @if($client->exists) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                <input type="text" name="first_name" value="{{ old('first_name', $client->first_name) }}" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Apellido *</label>
                <input type="text" name="last_name" value="{{ old('last_name', $client->last_name) }}" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono / WhatsApp *</label>
            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
            <input type="email" name="email" value="{{ old('email', $client->email) }}"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
            <input type="text" name="address" value="{{ old('address', $client->address) }}"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
            <select name="department"
                x-model="departmentId"
                @change="loadCities($event.target.value)"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Seleccionar departamento...</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ old('department', optional(\App\Models\Department::where('name', $client->department)->first())->id) == $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
            <select name="city"
                :disabled="!departmentId || loadingCities"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100">
                <option value="">
                    <span x-text="loadingCities ? 'Cargando...' : (departmentId ? 'Seleccionar ciudad...' : 'Primero selecciona departamento')"></span>
                </option>
                <template x-for="city in cities" :key="city.id">
                    <option :value="city.name"
                        :selected="city.name === '{{ $client->city }}'"
                        x-text="city.name">
                    </option>
                </template>
            </select>
        </div>

        <button type="submit"
            class="w-full bg-blue-700 text-white font-semibold py-3 rounded-xl">
            {{ $client->exists ? 'Guardar cambios' : 'Crear cliente' }}
        </button>
    </form>
</x-app-layout>