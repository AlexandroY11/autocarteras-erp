<x-app-layout title="Nueva Orden">
<div class="mt-4 mb-4 flex items-center gap-3">
    <a href="/dashboard" class="text-gray-500">← Volver</a>
    <h1 class="text-lg font-bold text-gray-800">Nueva Orden</h1>
</div>

<form method="POST" action="/production-orders"
    class="space-y-4"
    x-data="{
        clientMode: 'search',
        selectedClient: null,
        clientSearch: '',
        showDropdown: false,
        sticker: false,
        price: '',
        advance: 30000,
        departmentId: '',
        cities: [],
        loadingCities: false,
        searchResults: [],
        searching: false,

        async searchClients() {
            if (this.clientSearch.length < 2) { this.searchResults = []; return; }
            this.searching = true;
            const res = await fetch('/api/clients/search?q=' + encodeURIComponent(this.clientSearch));
            this.searchResults = await res.json();
            this.searching = false;
            this.showDropdown = true;
        },

        selectClient(client) {
            this.selectedClient = client;
            this.clientSearch = client.name + ' — ' + client.phone;
            this.showDropdown = false;
            this.clientMode = 'existing';
        },

        selectProduct(id) {
            const products = {{ $products->map(fn($p) => ['id' => $p->id, 'price' => $p->base_price])->toJson() }};
            const p = products.find(p => p.id == id);
            if (p && !this.price) this.price = p.price;
        },

        newClient() {
            this.clientMode = 'new';
            this.selectedClient = null;
            this.clientSearch = '';
        },

        resetClient() {
            this.clientMode = 'search';
            this.selectedClient = null;
            this.clientSearch = '';
            this.departmentId = '';
            this.cities = [];
            this.searchResults = [];
        },

        async loadCities(deptId) {
            this.departmentId = deptId; 
            if (!deptId) { this.cities = []; return; }
            this.loadingCities = true;
            const res = await fetch('/api/cities/' + deptId);
            this.cities = await res.json();
            this.loadingCities = false;
        }

    }">
    @csrf

    {{-- ── CLIENTE ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">👤 Cliente</h2>

        {{-- Búsqueda --}}
        <div x-show="clientMode === 'search'" class="space-y-2">
            <div class="relative">
                <input type="text"
                    x-model="clientSearch"
                    @input.debounce.400ms="searchClients()"
                    @click.outside="showDropdown = false"
                    placeholder="Buscar por nombre o teléfono..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                <div x-show="searching" class="absolute right-3 top-3 text-gray-400 text-xs">
                    Buscando...
                </div>

                <div x-show="showDropdown && searchResults.length > 0"
                    class="absolute z-10 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 overflow-hidden">
                    <template x-for="client in searchResults" :key="client.id">
                        <button type="button" @click="selectClient(client)"
                            class="w-full text-left px-4 py-3 hover:bg-blue-50 border-b border-gray-100 last:border-0">
                            <div class="text-sm font-medium" x-text="client.name"></div>
                            <div class="text-xs text-gray-400" x-text="client.phone + (client.city ? ' · ' + client.city : '')"></div>
                        </button>
                    </template>
                </div>
            </div>
            <button type="button" @click="newClient()"
                class="w-full text-center text-sm text-blue-600 border border-blue-200 rounded-lg py-2">
                + Cliente nuevo
            </button>
        </div>

        {{-- Cliente seleccionado --}}
        <div x-show="clientMode === 'existing'">
            <input type="hidden" name="client_id" :value="selectedClient?.id">
            <div class="flex justify-between items-center bg-blue-50 rounded-lg px-4 py-3">
                <div>
                    <div class="text-sm font-semibold text-blue-800" x-text="selectedClient?.name"></div>
                    <div class="text-xs text-blue-600" x-text="selectedClient?.phone"></div>
                </div>
                <button type="button" @click="resetClient()" class="text-xs text-gray-400 underline">Cambiar</button>
            </div>
        </div>

        {{-- Cliente nuevo --}}
        <div x-show="clientMode === 'new'" class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Nuevo cliente</span>
                <button type="button" @click="resetClient()" class="text-xs text-gray-400 underline">Cancelar</button>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs text-gray-500">Nombre *</label>
                    <input type="text" name="client_first_name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="text-xs text-gray-500">Apellido *</label>
                    <input type="text" name="client_last_name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-500">Teléfono / WhatsApp *</label>
                <input type="text" name="client_phone"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- DEPARTAMENTO -->
            <div>
                <label class="text-xs text-gray-500">Departamento</label>
                <x-searchable-select
                    name="client_department"
                    placeholder="Seleccionar departamento..."
                    :options="$departments->map(fn($d) => ['value' => (string)$d->id, 'label' => $d->name])->toArray()"
                    @selected.window="loadCities($event.detail.value)"
                />
            </div>

            <!-- CIUDAD -->
            <div>
                <label class="text-xs text-gray-500">Ciudad</label>
                
                <!-- Estado: No hay departamento -->
                <div x-show="!departmentId" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-400 bg-gray-50">
                    Primero selecciona departamento
                </div>

                <div x-show="departmentId">
                    <!-- Estado: Cargando -->
                    <div x-show="loadingCities" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-400 bg-gray-50">
                        Cargando ciudades...
                    </div>

                    <!-- El componente ahora recibe las ciudades mediante x-model -->
                    <div x-show="!loadingCities">
                        <x-searchable-select
                            name="client_city"
                            placeholder="Buscar ciudad..."
                            :options="[]" 
                            x-model="cities.map(c => ({ value: c.name, label: c.name }))"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── PRODUCTO ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">📦 Producto</h2>
        <select name="product_id" required
            @change="selectProduct($event.target.value)"
            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Seleccionar producto...</option>
            @foreach($products as $product)
            <option value="{{ $product->id }}">
                {{ $product->name }} — ${{ number_format($product->base_price, 0, ',', '.') }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- ── DETALLES ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">🎨 Detalles</h2>

        <div>
            <label class="text-xs text-gray-500">Color *</label>
            <x-searchable-select
                name="color"
                placeholder="Seleccionar color..."
                :options="[
                    ['value' => 'Negro', 'label' => 'Negro'],
                    ['value' => 'Gris', 'label' => 'Gris'],
                    ['value' => 'Beige', 'label' => 'Beige'],
                ]"
            />
        </div>

        <div class="border border-gray-200 rounded-xl p-3 space-y-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="sticker" value="1" x-model="sticker" class="w-4 h-4 text-blue-600">
                <span class="text-sm text-gray-700">¿Lleva calcomanía?</span>
            </label>
            <div x-show="sticker" x-transition>
                <label class="text-xs text-gray-500">Color de calcomanía</label>
                <x-searchable-select
                    name="sticker_color"
                    placeholder="Seleccionar color..."
                    :options="[
                        ['value' => 'Rojo', 'label' => 'Rojo'],
                        ['value' => 'Azul', 'label' => 'Azul'],
                        ['value' => 'Amarillo', 'label' => 'Amarillo'],
                        ['value' => 'Verde', 'label' => 'Verde'],
                        ['value' => 'Naranja', 'label' => 'Naranja'],
                        ['value' => 'Morado', 'label' => 'Morado'],
                        ['value' => 'Rosa', 'label' => 'Rosa'],
                        ['value' => 'Café', 'label' => 'Café'],
                        ['value' => 'Gris', 'label' => 'Gris'],
                        ['value' => 'Negro', 'label' => 'Negro'],
                        ['value' => 'Blanco', 'label' => 'Blanco'],
                        ['value' => 'Tornasol', 'label' => 'Tornasol'],
                        ['value' => 'Dorado', 'label' => 'Dorado'],
                        ['value' => 'Plateado', 'label' => 'Plateado'],
                    ]"
                />
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500">Fecha compromiso *</label>
            <input type="date" name="due_date" required
                value="{{ now()->addDays(7)->format('Y-m-d') }}"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="text-xs text-gray-500">Observaciones</label>
            <textarea name="observations" rows="2"
                placeholder="Detalles especiales del cliente..."
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
    </div>

    {{-- ── PAGO ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
        <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">💰 Pago</h2>

        <div>
            <label class="text-xs text-gray-500">Precio total *</label>
            <input type="number" name="price" x-model="price" required min="0" step="1000"
                placeholder="0"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-xs text-gray-400 mt-1">Se sugiere automáticamente al seleccionar el producto.</p>
        </div>

        <div>
            <label class="text-xs text-gray-500">Anticipo recibido</label>
            <input type="number" name="advance_payment" x-model="advance" min="0" step="1000"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="bg-gray-50 rounded-lg p-3 flex justify-between text-sm">
            <span class="text-gray-500">Saldo pendiente</span>
            <span class="font-bold text-red-600"
                x-text="'$' + Math.max(0, (parseFloat(price)||0) - (parseFloat(advance)||0)).toLocaleString('es-CO')">
            </span>
        </div>
    </div>

    <button type="submit"
        class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-4 rounded-xl text-base transition">
        Crear Orden
    </button>

</form>
</x-app-layout>