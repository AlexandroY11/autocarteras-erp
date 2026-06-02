<x-app-layout title="Nueva Orden">
    <div class="mt-4 mb-4 flex items-center gap-3"> 
        <a href="/dashboard" class="text-gray-500">← Volver</a>
        <h1 class="text-lg font-bold text-gray-800">Nueva Orden</h1>
    </div>
    
    <form method="POST" action="/production-orders" class="space-y-4" 
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
            holidays: [],
            dueDate: '',
            
            async init() {
                const year = new Date().getFullYear();
                try {
                    const res = await fetch(`/api/holidays/${year}`);
                    const data = await res.json();
                    this.holidays = data.map(h => {
                        const dateStr = typeof h === 'string' ? h : h.date;
                        return dateStr.substring(0, 10);
                    });
                    
                    console.log('Festivos procesados correctamente:', this.holidays);
                } catch (e) {
                    console.error('Error cargando festivos:', e);
                    this.holidays = [];
                }
                this.updateDueDate();
            },
            
            async searchClients() { 
                if (this.clientSearch.length < 2) { 
                    this.searchResults = []; 
                    return; 
                }
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
                if (p) { 
                    // Actualizamos el precio (esto disparará automáticamente el cambio en el input y el saldo)
                    this.price = parseFloat(p.price);
                    this.updateDueDate();
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
                this.cities = [];
                this.searchResults = []; 
            }, 
            
            async loadCities(deptId) { 
                this.departmentId = deptId; 
                if (!deptId) { 
                    this.cities = []; 
                    return; 
                }
                this.loadingCities = true; 
                const res = await fetch('/api/cities/' + deptId);
                this.cities = await res.json();
                this.loadingCities = false; 
            },
            
            updateDueDate() {
                let currentDate = new Date();
                let businessDaysAdded = 0;
                
                // Avanzar día por día hasta encontrar 15 días hábiles
                while (businessDaysAdded < 15) {
                    currentDate.setDate(currentDate.getDate() + 1);
                    
                    const dayOfWeek = currentDate.getDay(); // 0 = domingo
                    
                    // Formatear la fecha a YYYY-MM-DD para comparar con los festivos
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const day = String(currentDate.getDate()).padStart(2, '0');
                    const dateStr = `${year}-${month}-${day}`;
                    
                    // Verificar si es día hábil (no domingo y no festivo)
                    if (dayOfWeek !== 0 && !this.holidays.includes(dateStr)) {
                        businessDaysAdded++;
                    }
                }
                
                const finalYear = currentDate.getFullYear();
                const finalMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
                const finalDay = String(currentDate.getDate()).padStart(2, '0');
                
                this.dueDate = `${finalYear}-${finalMonth}-${finalDay}`;
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
            }

        }">
        
        @csrf 
        
        {{-- ── CLIENTE ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
            <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Cliente
            </h2>            
            <div x-show="clientMode === 'search'" class="space-y-2">
                <div class="relative"> 
                    <input type="text" x-model="clientSearch"
                            @input.debounce.400ms="searchClients()" @click.outside="showDropdown = false"
                            placeholder="Buscar por nombre o teléfono..."
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div x-show="searching" class="absolute right-3 top-3 text-gray-400 text-xs"> Buscando... </div>
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
                <div> 
                    <label class="text-xs text-gray-500">Dirección *</label> 
                    <input type="text" name="client_address" required
                            placeholder="Calle 123 # 45-67"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div> 
                    <label class="text-xs text-gray-500">Departamento</label> 
                    <x-searchable-select
                        name="client_department" 
                        placeholder="Seleccionar departamento..." 
                        :options="$departments->map(fn($d) => ['value' => (string) $d->id, 'label' => $d->name])->toArray()"
                        @selected.window="loadCities($event.detail.value)" /> 
                </div>
                <div> 
                    <label class="text-xs text-gray-500">Ciudad</label> 
                    <div x-show="!departmentId"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-400 bg-gray-50">
                        Primero selecciona departamento 
                    </div>
                    <div x-show="departmentId"> 
                        <div x-show="loadingCities"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-400 bg-gray-50">
                            Cargando ciudades... 
                        </div>
                        <div x-show="!loadingCities"> 
                            <!-- Usamos un select nativo estilizado para mayor compatibilidad con Alpine -->
                            <select name="client_city" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">Seleccionar ciudad...</option>
                                <template x-for="city in cities" :key="city.id || city.name">
                                    <option :value="city.name" x-text="city.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- ── PRODUCTO ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
            <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                Producto
            </h2>            
            <x-searchable-select
                name="product_id" 
                placeholder="Buscar producto..." 
                :options="$products->map(
                    fn($p) => [
                        'value' => (string) $p->id,
                        'label' => $p->name . ' — $' . number_format($p->base_price, 0, ',', '.'),
                    ],
                )->toArray()"
                @selected.window="selectProduct($event.detail.value)" />
        </div> 
        
        {{-- ── DETALLES ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 space-y-3">
            <h2 class="flex items-center gap-2 font-semibold text-gray-700 text-sm uppercase tracking-wide">
                <svg xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                Detalles
            </h2>           
            <div> 
                <label class="text-xs text-gray-500">Color *</label> 
                <x-searchable-select name="color"
                    placeholder="Seleccionar color..." :options="[
                        ['value' => 'Negro', 'label' => 'Negro'],
                        ['value' => 'Gris', 'label' => 'Gris'],
                        ['value' => 'Beige', 'label' => 'Beige'],
                    ]" /> 
            </div>
            <div class="border border-gray-200 rounded-xl p-3 space-y-2"> 
                <label class="flex items-center gap-2 cursor-pointer"> 
                    <input type="checkbox" name="sticker" value="1"
                        x-model="sticker" class="w-4 h-4 text-blue-600"> 
                    <span class="text-sm text-gray-700">¿Lleva calcomanía?</span> 
                </label>
                <div x-show="sticker" x-transition> 
                    <label class="text-xs text-gray-500">Color de calcomanía</label>
                    <x-searchable-select name="sticker_color" 
                        placeholder="Seleccionar color..." :options="[
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
                        ]" />
                </div>
            </div>
            <div> 
                <label class="text-xs text-gray-500">Fecha compromiso *</label> 
                <input type="date"
                    name="due_date" 
                    required 
                    x-model="dueDate"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Calculada automáticamente: 15 días hábiles (excluyendo domingos y festivos de Colombia)</p>
            </div>
            <div> 
                <label class="text-xs text-gray-500">Observaciones</label>
                <textarea name="observations" rows="2" placeholder="Detalles especiales del cliente..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
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
                {{-- Input de Precio --}}
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Precio Total</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400 text-sm">$</span>
                        <input type="number" name="price" x-model.number="price" required
                            class="w-full border border-gray-300 rounded-lg pl-7 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-semibold">
                    </div>
                </div>

                {{-- Input de Adelanto --}}
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Adelanto / Abono</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400 text-sm">$</span>
                        <input type="number" name="advance" x-model.number="advance" required
                            class="w-full border border-gray-300 rounded-lg pl-7 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-green-600 font-semibold">
                    </div>
                </div>
            </div>

            {{-- Cuadro de Saldo Pendiente --}}
            <div class="bg-gray-50 rounded-xl p-4 flex justify-between items-center border border-dashed border-gray-200">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Saldo Pendiente</p>
                    <p class="text-2xl font-bold text-gray-800" x-text="formatCurrency(getBalance( ))"></p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                        Cobro en entrega
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