<x-app-layout title="Nueva Orden">
    <div class="mt-4 mb-4 flex items-center gap-3"> 
        <a href="/orders" class="text-gray-500">← Volver</a>
        <h1 class="text-lg font-bold text-gray-800">Nueva Orden</h1>
    </div>
    
    <form method="POST" action="/production-orders" class="space-y-4" 
        x-data="{ 
            clientMode: '{{ old('client_phone') || old('client_first_name') ? 'new' : 'search' }}', 
            selectedClient: null, 
            clientSearch: '{{ old('client_phone') ? old('client_first_name').' '.old('client_last_name') : '' }}', 
            showDropdown: false, 
            sticker: {{ old('sticker') ? 'true' : 'false' }}, 
            price: {{ old('price', 0) }}, 
            advance: {{ old('advance_payment', 30000) }}, 
            departmentId: '{{ old('client_department', '') }}', 
            cityId: '{{ old('client_city', '') }}',
            cities: [], 
            loadingCities: false, 
            searchResults: [], 
            searching: false,
            holidays: [],
            dueDate: '{{ old('due_date', '') }}',
            
            async init() {
                const year = new Date().getFullYear();
                try {
                    const res = await fetch(`/api/holidays/${year}`);
                    const data = await res.json();
                    this.holidays = data.map(h => (typeof h === 'string' ? h : h.date).substring(0, 10));
                } catch (e) {
                    console.error('Error cargando festivos:', e);
                    this.holidays = [];
                }

                // Si es un error de validación y había departamento, recargar ciudades
                if (this.departmentId) {
                    await this.loadCities(this.departmentId, this.cityId);
                }

                // Solo calcular fecha si no viene de un 'old'
                if (!this.dueDate) this.updateDueDate();
            },
            
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
                console.log('ID recibido:', id);

                const products = {{ $products->map(fn($p) => [
                    'id' => $p->id,
                    'price' => $p->base_price
                ])->toJson() }};

                const p = products.find(p => p.id == id);

                if (p) {
                    this.price = parseFloat(p.price);
                    console.log('Precio actualizado:', this.price);
                } else {
                    console.error('Producto no encontrado en la lista');
                }
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
                this.cityId = '';
                this.searchResults = []; 
            }, 
            
            async loadCities(deptId, preselectedCityId = '') { 
                this.departmentId = deptId; 
                // Si no estamos cargando un valor previo, limpiamos la ciudad actual
                if (!preselectedCityId) this.cityId = ''; 

                window.dispatchEvent(new CustomEvent('searchable-options-city', {
                    detail: { options: [], disabled: true, selected: '' }
                }));

                if (!deptId) return; 

                this.loadingCities = true; 
                try {
                    const res = await fetch('/api/cities/' + deptId);
                    const data = await res.json();
                    const options = data.map(c => ({ value: String(c.id), label: c.name }));

                    window.dispatchEvent(new CustomEvent('searchable-options-city', {
                        detail: {
                            options,
                            disabled: false,
                            selected: preselectedCityId || this.cityId 
                        }
                    }));
                } catch (e) {
                    window.showAlert.error('No se pudieron cargar las ciudades');
                } finally {
                    this.loadingCities = false;
                }
            },

            updateDueDate() {
                let currentDate = new Date();
                let businessDaysAdded = 0;
                while (businessDaysAdded < 15) {
                    currentDate.setDate(currentDate.getDate() + 1);
                    const dayOfWeek = currentDate.getDay();
                    const dateStr = currentDate.toISOString().split('T')[0];
                    if (dayOfWeek !== 0 && !this.holidays.includes(dateStr)) {
                        businessDaysAdded++;
                    }
                }
                this.dueDate = currentDate.toISOString().split('T')[0];
            },
            
            getBalance() {
                const priceNum = parseFloat(this.price) || 0;
                const advanceNum = parseFloat(this.advance) || 0;
                return Math.max(0, priceNum - advanceNum);
            },

            formatCurrency(value) {
                return new Intl.NumberFormat('es-CO', {
                    style: 'currency',
                    currency: 'COP',
                    minimumFractionDigits: 0
                }).format(value);
            },

            submit(e) {
                // Validaciones de UI antes de enviar
                if (this.clientMode === 'search' && !this.selectedClient) {
                    e.preventDefault();
                    window.showAlert.warning('Debes seleccionar un cliente o registrar uno nuevo.');
                    return;
                }
                
                if (this.clientMode === 'new' && !this.cityId) {
                    e.preventDefault();
                    window.showAlert.warning('Selecciona ciudad y departamento para el nuevo cliente.');
                    return;
                }

                window.showAlert.confirm(e, '¿Confirmar creación de la orden?');
            }
        }" 
        @submit="submit($event)">

        
        @csrf 
        
        {{-- ── CLIENTE ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
            <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Cliente
            </h2>            
            
            {{-- Buscador --}}
            <div x-show="clientMode === 'search'" class="space-y-2">
                <div class="relative"> 
                    <input type="text" x-model="clientSearch"
                            @input.debounce.400ms="searchClients()" @click.outside="showDropdown = false"
                            placeholder="Buscar por nombre o teléfono..."
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('client_id') border-red-500 @enderror">
                    
                    @error('client_id')
                        <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p>
                    @enderror

                    <div x-show="searching" class="absolute right-3 top-3 text-gray-400 text-xs"> Buscando... </div>
                    {{-- Resultados Dropdown --}}
                    <div x-show="showDropdown && searchResults.length > 0"
                        class="absolute z-10 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 overflow-hidden">
                        <template x-for="client in searchResults" :key="client.id"> 
                            <button type="button" @click="selectClient(client)"
                                    class="w-full text-left px-4 py-3 hover:bg-blue-50 border-b border-gray-100 last:border-0">
                                <div class="text-sm font-medium" x-text="client.name"></div>
                                <div class="text-xs text-gray-400"
                                    x-text="client.phone + (client.city ? ' · ' + client.city : '')"></div>
                            </button> 
                        </template> 
                    </div>
                </div> 
                <button type="button" @click="newClient()"
                    class="w-full text-center text-sm text-blue-600 border border-blue-200 rounded-lg py-2"> 
                    + Cliente nuevo 
                </button>
            </div> 
            
            {{-- Cliente Seleccionado --}}
            <div x-show="clientMode === 'existing'" x-cloak> 
                <input type="hidden" name="client_id" :value="selectedClient?.id">
                <div class="flex justify-between items-center bg-blue-50 rounded-lg px-4 py-3 border border-blue-100">
                    <div>
                        <div class="text-sm font-semibold text-blue-800" x-text="selectedClient?.name"></div>
                        <div class="text-xs text-blue-600" x-text="selectedClient?.phone"></div>
                    </div> 
                    <button type="button" @click="resetClient()" class="text-xs text-gray-400 underline">Cambiar</button>
                </div>
            </div> 
            
            {{-- Formulario Cliente Nuevo --}}
            <div x-show="clientMode === 'new'" class="space-y-3" x-cloak>
                <div class="flex justify-between items-center"> 
                    <span class="text-sm font-medium text-blue-700 bg-blue-50 px-2 py-1 rounded-md">Nuevo cliente</span> 
                    <button type="button" @click="resetClient()" class="text-xs text-gray-400 underline">Cancelar</button> 
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div> 
                        <label class="text-xs font-bold text-gray-400 uppercase">Nombre *</label> 
                        <input type="text" name="client_first_name" value="{{ old('client_first_name') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm @error('client_first_name') border-red-500 @else border-gray-300 @enderror">
                        @error('client_first_name') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div> 
                        <label class="text-xs font-bold text-gray-400 uppercase">Apellido *</label> 
                        <input type="text" name="client_last_name" value="{{ old('client_last_name') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm @error('client_last_name') border-red-500 @else border-gray-300 @enderror">
                        @error('client_last_name') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div> 
                    <label class="text-xs font-bold text-gray-400 uppercase">Teléfono / WhatsApp *</label> 
                    <input type="text" name="client_phone" value="{{ old('client_phone') }}"
                            class="w-full border rounded-lg px-3 py-2 text-sm @error('client_phone') border-red-500 @else border-gray-300 @enderror">
                    @error('client_phone') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                </div>
                <div> 
                    <label class="text-xs font-bold text-gray-400 uppercase">Dirección *</label> 
                    <input type="text" name="client_address" value="{{ old('client_address') }}"
                            placeholder="Calle 123 # 45-67"
                            class="w-full border rounded-lg px-3 py-2 text-sm @error('client_address') border-red-500 @else border-gray-300 @enderror">
                    @error('client_address') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1" @@selected="loadCities($event.detail.value)">
                        <label class="text-xs font-bold text-gray-400 uppercase">Dpto *</label>
                        <x-searchable-select name="client_department" :selected="old('client_department')" :options="$departments->map(fn($d) => ['value' => (string)$d->id, 'label' => $d->name])->toArray()" />
                        @error('client_department') <p class="text-red-500 text-[10px] font-bold">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1" @@selected="cityId = $event.detail.value">
                        <label class="text-xs font-bold text-gray-400 uppercase">Ciudad *</label>
                        <x-searchable-select name="client_city" listen-key="city" :disabled="true" :options="[]" />
                        @error('client_city') <p class="text-red-500 text-[10px] font-bold">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PRODUCTO ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
            <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
                <svg xmlns="http://www.w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                Producto
            </h2>            
            <div x-on:selected="selectProduct($event.detail.value)">
                <x-searchable-select
                    name="product_id"
                    placeholder="Buscar producto..." 
                    :selected="old('product_id')"
                    :options="$products->map(fn($p) => ['value' => (string) $p->id, 'label' => $p->name . ' — $' . number_format($p->base_price, 0, ',', '.')])->toArray()"
                />
            </div>
            @error('product_id') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- ── DETALLES ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
            <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                Detalles
            </h2>           
            <div> 
                <label class="text-xs font-bold text-gray-400 uppercase">Color *</label> 
                <x-searchable-select name="color" :selected="old('color')"
                    placeholder="Seleccionar color..." :options="[
                        ['value' => 'Negro', 'label' => 'Negro'],
                        ['value' => 'Gris', 'label' => 'Gris'],
                        ['value' => 'Beige', 'label' => 'Beige'],
                    ]" /> 
                @error('color') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="border border-gray-200 rounded-xl p-3 space-y-2"> 
                <label class="flex items-center gap-2 cursor-pointer"> 
                    {{-- Quitamos el inline 'checked' porque x-model se encarga basándose en el init del x-data --}}
                    <input type="checkbox" name="sticker" value="1"
                        x-model="sticker" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"> 
                    <span class="text-sm font-semibold text-gray-700">¿Lleva calcomanía?</span> 
                </label>
                <div x-show="sticker" x-transition x-cloak class="mt-2"> 
                    <label class="text-xs font-bold text-gray-400 uppercase">Color de calcomanía</label>
                    <x-searchable-select name="sticker_color" :selected="old('sticker_color')"
                        placeholder="Seleccionar color..." :options="[['value' => 'Rojo', 'label' => 'Rojo'], ['value' => 'Azul', 'label' => 'Azul'], ['value' => 'Dorado', 'label' => 'Dorado'], ['value' => 'Plateado', 'label' => 'Plateado'], ['value' => 'Negro', 'label' => 'Negro']]" />
                    @error('sticker_color') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div> 
                <label class="text-xs font-bold text-gray-400 uppercase">Fecha compromiso *</label> 
                <input type="date" name="due_date" required x-model="dueDate"
                    class="w-full border rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 @error('due_date') border-red-500 @else border-gray-300 @enderror">
                @error('due_date') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                <p class="text-[10px] text-gray-400 mt-1 leading-tight italic">Calculada: 15 días hábiles (sin domingos ni festivos)</p>
            </div>

            <div> 
                <label class="text-xs font-bold text-gray-400 uppercase">Observaciones</label>
                <textarea name="observations" rows="2" placeholder="Ej: Entrega en portería, empaque especial..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">{{ old('observations') }}</textarea>
                @error('observations') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
            </div>
        </div>


        {{-- ── PAGO ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-4">
            <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75m0 1.5v.75m0 1.5v.75m10.5-4.5v.75m0 1.5v.75m0 1.5v.75m-9-6h9a2.25 2.25 0 0 1 2.25 2.25v13.5a2.25 2.25 0 0 1-2.25 2.25h-9a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 3.75 4.5Z" />
                </svg>
                Pago y Saldo
            </h2>

            <div class="grid grid-cols-2 gap-4">
                {{-- Precio Total --}}
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Precio Total *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400 text-sm">$</span>
                        <input type="number" 
                            name="price" 
                            x-model.number="price" 
                            required
                            class="w-full border rounded-lg pl-7 pr-4 py-3 text-sm font-black focus:ring-2 focus:ring-blue-500 @error('price') border-red-500 @else border-gray-300 @enderror">
                    </div>
                    @error('price') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Adelanto / Abono --}}
                <div>
                    <label class="text-[10px] font-black text-gray-400 uppercase mb-1 block">Abono Inicial</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400 text-sm">$</span>
                        <input type="number" 
                            name="advance_payment" 
                            x-model.number="advance" 
                            class="w-full border rounded-lg pl-7 pr-4 py-3 text-sm text-green-600 font-black focus:ring-2 focus:ring-blue-500 @error('advance_payment') border-red-500 @else border-gray-300 @enderror">
                    </div>
                    @error('advance_payment') <p class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Cuadro de Saldo Pendiente --}}
            <div class="bg-gray-900 rounded-2xl p-4 flex justify-between items-center shadow-lg border-t border-white/10">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-black">Saldo a Cobrar</p>
                    <p class="text-2xl font-black text-white" x-text="formatCurrency(getBalance())"></p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center rounded-full bg-blue-500/20 px-3 py-1 text-[10px] font-black text-blue-400 uppercase tracking-tighter ring-1 ring-inset ring-blue-400/30">
                        Contra entrega
                    </span>
                </div>
            </div>
        </div>

        <button type="submit"
            class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-4 rounded-xl text-base transition">
            Crear Orden 
        </button> 
    </form>
</x-app-layout>