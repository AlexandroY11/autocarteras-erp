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

    <form method="POST" action="{{ $client->exists ? route('clients.update', $client) : route('clients.store') }}" class="space-y-4" x-data="{
        departmentId: '{{ old('department_id', $client->department_id ?? '') }}',
        cityId: '{{ old('city_id', $client->city_id ?? '') }}',
        loadingCities: false,

        async loadCities(deptId) {
            // Primero deshabilita y limpia ciudades
            window.dispatchEvent(new CustomEvent('searchable-options-city', {
                detail: { options: [], disabled: true, selected: '' }
            }));

            if (!deptId) return;
            this.loadingCities = true;

            const res = await fetch('/api/cities/' + deptId);
            const data = await res.json();

            const options = data.map(c => ({ value: String(c.id), label: c.name }));

            window.dispatchEvent(new CustomEvent('searchable-options-city', {
                detail: {
                    options,
                    disabled: false,
                    selected: this.cityId  // preselecciona si venía de edición
                }
            }));

            this.loadingCities = false;
        },

        async init() {
            if (this.departmentId) {
                await this.loadCities(this.departmentId);
            }
        },
        submit(e) {
            if (!this.departmentId) {
                e.preventDefault();
                showAlert.error('Debes seleccionar un departamento antes de continuar', 'Campo requerido');
                return;
            }
            
            if (!this.cityId) {
                e.preventDefault();
                showAlert.error('Debes seleccionar una ciudad para el envío', 'Campo requerido');
                return;
            }

            // Opcional: Si quieres pedir confirmación final antes de que se envíe realmente
            showAlert.confirm(e, '¿Estás seguro de guardar los datos de este cliente?');
        }

    }" @submit="submit($event)">
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
                    value="{{ old('address', $client->address) }}" required 
                    placeholder="Ej: Cra 5 # 12-34"
                    class="input-field">
            </div>

            {{-- Departamento --}}
            <div @@selected="departmentId = $event.detail.value; cityId = ''; loadCities($event.detail.value)">
                <x-searchable-select
                    name="department_id"
                    placeholder="Seleccionar departamento..."
                    :selected="old('department_id', $client->department_id ?? '')"
                    :options="$departments->map(fn($d) => ['value' => (string)$d->id, 'label' => $d->name])->values()->toArray()"
                />
            </div>

            {{-- Ciudad --}}
            <div @@selected="cityId = $event.detail.value">
                <x-searchable-select
                    name="city_id"
                    placeholder="Seleccionar ciudad..."
                    :selected="old('city_id', $client->city_id ?? '')"
                    :options="[]"
                    :disabled="true"
                    listen-key="city"
                />
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