<x-app-layout title="Nueva Orden">

<div class="pt-4 space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center gap-3">
        <a href="/dashboard"
           class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-gray-500 hover:text-blue-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                      d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
        </a>

        <div>
            <h1 class="text-2xl font-black text-gray-900">Nueva Orden</h1>
            <p class="text-sm text-gray-500">Registro de producción</p>
        </div>
    </div>

    <form method="POST"
          action="/production-orders"
          class="space-y-6"
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

        {{-- ================= CLIENTE ================= --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-5 space-y-4">

            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Cliente</h2>
            </div>

            {{-- SEARCH CLIENT --}}
            <div x-show="clientMode === 'search'" class="space-y-3">

                <div class="relative">
                    <input type="text"
                           x-model="clientSearch"
                           @input.debounce.400ms="searchClients()"
                           @click.outside="showDropdown = false"
                           placeholder="Buscar cliente..."
                           class="w-full bg-gray-50 border-none rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500">

                    <div x-show="searching"
                         class="absolute right-4 top-3 text-xs text-gray-400">
                        Buscando...
                    </div>

                    <div x-show="showDropdown && searchResults.length"
                         class="absolute z-10 w-full bg-white border border-gray-100 rounded-2xl shadow-lg mt-2 overflow-hidden">

                        <template x-for="client in searchResults" :key="client.id">
                            <button type="button"
                                    @click="selectClient(client)"
                                    class="w-full text-left px-4 py-3 hover:bg-blue-50 border-b border-gray-100 last:border-0">

                                <div class="text-sm font-semibold" x-text="client.name"></div>
                                <div class="text-xs text-gray-400"
                                     x-text="client.phone + (client.city ? ' · ' + client.city : '')"></div>
                            </button>
                        </template>

                    </div>
                </div>

                <button type="button"
                        @click="newClient()"
                        class="w-full border border-blue-200 text-blue-600 font-semibold py-2 rounded-xl text-sm">
                    + Crear cliente nuevo
                </button>

            </div>

            {{-- CLIENTE EXISTENTE --}}
            <div x-show="clientMode === 'existing'">
                <input type="hidden" name="client_id" :value="selectedClient?.id">

                <div class="flex justify-between items-center bg-blue-50 rounded-2xl px-4 py-3">
                    <div>
                        <div class="text-sm font-semibold text-blue-800" x-text="selectedClient?.name"></div>
                        <div class="text-xs text-blue-600" x-text="selectedClient?.phone"></div>
                    </div>

                    <button type="button"
                            @click="resetClient()"
                            class="text-xs text-gray-500 underline">
                        Cambiar
                    </button>
                </div>
            </div>

            {{-- NUEVO CLIENTE --}}
            <div x-show="clientMode === 'new'" class="space-y-3">

                <div class="flex justify-between items-center">
                    <span class="text-sm font-semibold text-gray-700">Nuevo cliente</span>
                    <button type="button"
                            @click="resetClient()"
                            class="text-xs text-gray-500 underline">
                        Cancelar
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="client_first_name" placeholder="Nombre"
                           class="bg-gray-50 rounded-xl px-3 py-2 text-sm border-none">

                    <input type="text" name="client_last_name" placeholder="Apellido"
                           class="bg-gray-50 rounded-xl px-3 py-2 text-sm border-none">
                </div>

                <input type="text" name="client_phone" placeholder="Teléfono"
                       class="w-full bg-gray-50 rounded-xl px-3 py-2 text-sm border-none">

                {{-- DEPARTAMENTO --}}
                <x-searchable-select
                    name="client_department"
                    placeholder="Departamento"
                    :options="$departments->map(fn($d)=>['value'=>(string)$d->id,'label'=>$d->name])->toArray()"
                    @selected.window="loadCities($event.detail.value)"
                />

                {{-- CIUDAD --}}
                <div>
                    <div x-show="!departmentId"
                         class="bg-gray-50 rounded-xl px-3 py-2 text-sm text-gray-400">
                        Selecciona departamento
                    </div>

                    <div x-show="departmentId">
                        <div x-show="loadingCities"
                             class="bg-gray-50 rounded-xl px-3 py-2 text-sm text-gray-400">
                            Cargando...
                        </div>

                        <div x-show="!loadingCities">
                            <x-searchable-select
                                name="client_city"
                                placeholder="Ciudad"
                                :options="[]"
                                x-model="cities.map(c => ({ value: c.name, label: c.name }))"
                            />
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- ================= PRODUCTO ================= --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-5">

            <h2 class="text-sm font-bold text-gray-700 uppercase mb-3">Producto</h2>

            <x-searchable-select
                name="product_id"
                placeholder="Seleccionar producto"
                :options="$products->map(fn($p)=>[
                    'value'=>(string)$p->id,
                    'label'=>$p->name.' — $'.number_format($p->base_price,0,',','.')
                ])->toArray()"
                @selected.window="selectProduct($event.detail.value)"
            />

        </div>

        {{-- ================= DETALLES ================= --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-5 space-y-4">

            <h2 class="text-sm font-bold text-gray-700 uppercase">Detalles</h2>

            <x-searchable-select
                name="color"
                placeholder="Color"
                :options="[
                    ['value'=>'Negro','label'=>'Negro'],
                    ['value'=>'Gris','label'=>'Gris'],
                    ['value'=>'Beige','label'=>'Beige'],
                ]"
            />

            {{-- STICKER --}}
            <div class="border border-gray-100 rounded-2xl p-4 space-y-2">

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="sticker" value="1" x-model="sticker">
                    <span class="text-sm">Incluye calcomanía</span>
                </label>

                <div x-show="sticker">
                    <x-searchable-select
                        name="sticker_color"
                        placeholder="Color sticker"
                        :options="[
                            ['value'=>'Rojo','label'=>'Rojo'],
                            ['value'=>'Azul','label'=>'Azul'],
                            ['value'=>'Amarillo','label'=>'Amarillo'],
                            ['value'=>'Verde','label'=>'Verde'],
                            ['value'=>'Naranja','label'=>'Naranja'],
                            ['value'=>'Morado','label'=>'Morado'],
                            ['value'=>'Rosa','label'=>'Rosa'],
                            ['value'=>'Café','label'=>'Café'],
                            ['value'=>'Gris','label'=>'Gris'],
                            ['value'=>'Negro','label'=>'Negro'],
                            ['value'=>'Blanco','label'=>'Blanco'],
                            ['value'=>'Tornasol','label'=>'Tornasol'],
                            ['value'=>'Dorado','label'=>'Dorado'],
                            ['value'=>'Plateado','label'=>'Plateado'],
                        ]"
                    />
                </div>

            </div>

            <input type="date"
                   name="due_date"
                   value="{{ now()->addDays(7)->format('Y-m-d') }}"
                   class="w-full bg-gray-50 rounded-xl px-3 py-2 text-sm">

            <textarea name="observations"
                      placeholder="Observaciones"
                      class="w-full bg-gray-50 rounded-xl px-3 py-2 text-sm"></textarea>

        </div>

        {{-- ================= PAGO ================= --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-5 space-y-3">

            <h2 class="text-sm font-bold text-gray-700 uppercase">Pago</h2>

            <input type="number"
                   name="price"
                   x-model="price"
                   placeholder="Precio total"
                   class="w-full bg-gray-50 rounded-xl px-3 py-3 text-xl font-black text-blue-700">

            <input type="number"
                   name="advance_payment"
                   x-model="advance"
                   placeholder="Anticipo"
                   class="w-full bg-gray-50 rounded-xl px-3 py-2">

            <div class="flex justify-between text-sm bg-gray-50 rounded-xl p-3">
                <span>Saldo</span>
                <span class="font-black text-red-600"
                      x-text="'$' + Math.max(0,(parseFloat(price)||0)-(parseFloat(advance)||0)).toLocaleString('es-CO')">
                </span>
            </div>

        </div>

        {{-- SUBMIT --}}
        <button type="submit"
                class="w-full bg-blue-700 text-white font-black py-4 rounded-2xl">
            Crear Orden
        </button>

    </form>

</div>

</x-app-layout>