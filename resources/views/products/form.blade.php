<x-app-layout title="Nueva Orden">
<div class="max-w-3xl mx-auto pt-4 pb-12" x-data="{
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
        try {
            const res = await fetch('/api/cities/' + deptId);
            this.cities = await res.json();
        } finally {
            this.loadingCities = false;
        }
    }
}">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="/dashboard" class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-blue-600 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Nueva Orden</h1>
            <p class="text-sm text-gray-500 font-medium">Completa los datos para iniciar la producción</p>
        </div>
    </div>

    <form method="POST" action="/production-orders" class="space-y-6">
        @csrf

        {{-- SECCIÓN 1: CLIENTE --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-6 md:p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 rounded-full -mr-16 -mt-16"></div>
            
            <div class="flex items-center gap-3 mb-6 relative">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Información del Cliente</h2>
            </div>

            {{-- Búsqueda --}}
            <div x-show="clientMode === 'search'" class="space-y-4 relative">
                <div class="relative">
                    <input type="text" x-model="clientSearch" @input.debounce.400ms="searchClients()" @click.outside="showDropdown = false"
                        placeholder="Buscar por nombre o teléfono..."
                        class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 pl-12 text-sm focus:ring-2 focus:ring-blue-500 transition-all">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                    <div x-show="searching" class="absolute right-4 top-1/2 -translate-y-1/2">
                        <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
                    </div>

                    <div x-show="showDropdown && searchResults.length > 0"
                        class="absolute z-30 w-full bg-white border border-gray-100 rounded-2xl shadow-xl mt-2 overflow-hidden py-2">
                        <template x-for="client in searchResults" :key="client.id">
                            <button type="button" @click="selectClient(client)"
                                class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors border-b border-gray-50 last:border-0">
                                <div class="text-sm font-bold text-gray-800" x-text="client.name"></div>
                                <div class="text-xs text-gray-400 font-medium" x-text="client.phone + (client.city ? ' · ' + client.city : '')"></div>
                            </button>
                        </template>
                    </div>
                </div>
                <button type="button" @click="newClient()"
                    class="w-full flex items-center justify-center gap-2 text-sm text-blue-600 font-bold bg-blue-50 rounded-2xl py-4 hover:bg-blue-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Crear nuevo cliente
                </button>
            </div>

            {{-- Cliente seleccionado --}}
            <div x-show="clientMode === 'existing'" class="bg-blue-50 rounded-3xl p-5 border border-blue-100 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white font-black text-lg">
                        <span x-text="selectedClient?.name.charAt(0)"></span>
                    </div>
                    <div>
                        <div class="text-base font-bold text-blue-900" x-text="selectedClient?.name"></div>
                        <div class="text-sm font-medium text-blue-600" x-text="selectedClient?.phone"></div>
                    </div>
                </div>
                <button type="button" @click="resetClient()" class="text-xs font-bold text-blue-400 hover:text-blue-600 underline">Cambiar</button>
                <input type="hidden" name="client_id" :value="selectedClient?.id">
            </div>

            {{-- Formulario Nuevo Cliente --}}
            <div x-show="clientMode === 'new'" x-transition class="space-y-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-lg">Nuevo Registro</span>
                    <button type="button" @click="resetClient()" class="text-xs font-bold text-gray-400 underline">Cancelar</button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Nombre *</label>
                        <input type="text" name="client_first_name" class="w-full bg-gray-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Apellido *</label>
                        <input type="text" name="client_last_name" class="w-full bg-gray-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Teléfono / WhatsApp *</label>
                    <input type="text" name="client_phone" class="w-full bg-gray-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Departamento</label>
                        <x-searchable-select name="client_department" placeholder="Seleccionar..."
                            :options="$departments->map(fn($d) => ['value' => (string)$d->id, 'label' => $d->name])->toArray()"
                            @selected.window="loadCities($event.detail.value)"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Ciudad</label>
                        <div x-show="!departmentId" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm text-gray-400">Elegir depto.</div>
                        <div x-show="departmentId">
                            <div x-show="loadingCities" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm text-gray-400 animate-pulse">Cargando...</div>
                            <x-searchable-select x-show="!loadingCities" name="client_city" placeholder="Buscar ciudad..."
                                :options="[]" x-model="cities.map(c => ({ value: c.name, label: c.name }))"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2: PRODUCTO Y DETALLES --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-purple-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Detalles del Producto</h2>
            </div>

            <div class="space-y-5">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Producto *</label>
                    <select name="product_id" required @change="selectProduct($event.target.value)"
                        class="w-full bg-gray-50 border-none rounded-2xl px-4 py-4 text-sm focus:ring-2 focus:ring-purple-500 font-bold text-gray-700">
                        <option value="">Seleccionar producto del catálogo...</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->name }} — ${{ number_format($product->base_price, 0, ',', '.') }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Color Principal *</label>
                        <x-searchable-select name="color" placeholder="Elegir color..."
                            :options="[['value' => 'Negro', 'label' => 'Negro'], ['value' => 'Gris', 'label' => 'Gris'], ['value' => 'Beige', 'label' => 'Beige']]"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Fecha de Entrega *</label>
                        <input type="date" name="due_date" required value="{{ now()->addDays(7)->format('Y-m-d') }}"
                            class="w-full bg-gray-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 font-bold text-gray-700">
                    </div>
                </div>

                {{-- Calcomanía --}}
                <div class="bg-gray-50 rounded-3xl p-5 space-y-4 border border-gray-100">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="sticker" value="1" x-model="sticker" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-purple-700 transition-colors">¿Incluye Calcomanía Personalizada?</span>
                    </label>
                    
                    <div x-show="sticker" x-transition class="pt-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Color de Calcomanía</label>
                        <x-searchable-select name="sticker_color" placeholder="Elegir color..."
                            :options="[['value' => 'Rojo', 'label' => 'Rojo'], ['value' => 'Azul', 'label' => 'Azul'], ['value' => 'Dorado', 'label' => 'Dorado'], ['value' => 'Plateado', 'label' => 'Plateado'], ['value' => 'Tornasol', 'label' => 'Tornasol']]"
                        />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Observaciones Especiales</label>
                    <textarea name="observations" rows="3" placeholder="Ej: Ajustes de medida, bordado especial, etc..."
                        class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 text-sm focus:ring-2 focus:ring-purple-500"></textarea>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 3: PAGO Y RESUMEN --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 p-6 md:p-8 relative overflow-hidden">
            <div class="absolute bottom-0 left-0 w-full h-2 bg-blue-700"></div>
            
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75m0 1.5v.75m0 1.5v.75m0 1.5V15m1.5 1.5h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m1.5 0h1.5m-1.5-1.5V4.5m0 12h-15" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-800">Resumen Financiero</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Precio Total de Venta *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">$</span>
                        <input type="number" name="price" x-model="price" required min="0" step="1000"
                            class="w-full bg-gray-50 border-none rounded-2xl px-8 py-4 text-lg font-black text-gray-900 focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase ml-2">Anticipo Recibido</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">$</span>
                        <input type="number" name="advance_payment" x-model="advance" min="0" step="1000"
                            class="w-full bg-gray-50 border-none rounded-2xl px-8 py-4 text-lg font-black text-emerald-600 focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            {{-- Saldo Final --}}
            <div class="bg-gray-900 rounded-3xl p-6 flex justify-between items-center shadow-lg shadow-gray-200">
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Saldo Pendiente</p>
                    <p class="text-xs text-gray-500 font-medium">A cobrar contra entrega</p>
                </div>
                <div class="text-right">
                    <span class="text-3xl font-black text-white"
                        x-text="'$' + Math.max(0, (parseFloat(price)||0) - (parseFloat(advance)||0)).toLocaleString('es-CO')">
                    </span>
                </div>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-blue-700 hover:bg-blue-800 text-white font-black py-5 rounded-[2rem] text-lg transition-all shadow-xl shadow-blue-200 active:scale-[0.98]">
            Crear Orden de Producción
        </button>

    </form>
</div>
</x-app-layout>
