<x-app-layout title="Nueva Orden">
<div class="pt-4 space-y-5">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Nueva orden</h1>
            <p class="text-sm text-gray-500">Registra una orden de producción</p>
        </div>

        <a href="/dashboard"
           class="text-sm text-gray-500 hover:text-blue-600 font-medium">
            ← Volver
        </a>
    </div>

    <form method="POST" action="/production-orders"
          class="space-y-5"
          x-data="orderForm()">

        @csrf

        {{-- ================= CLIENTE ================= --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 space-y-4">

            <div class="flex items-center justify-between">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Cliente</h2>
            </div>

            {{-- BUSCADOR CLIENTE --}}
            <div x-show="clientMode === 'search'" class="space-y-3">

                <div class="relative">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>

                    <input type="text"
                           x-model="clientSearch"
                           @input.debounce.300ms="searchClients()"
                           placeholder="Buscar cliente..."
                           class="w-full bg-gray-50 border-none rounded-2xl px-5 py-3.5 pl-12 text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- RESULTADOS --}}
                <div x-show="searchResults.length"
                     class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">

                    <template x-for="c in searchResults" :key="c.id">
                        <button type="button"
                                @click="selectClient(c)"
                                class="w-full text-left px-4 py-3 hover:bg-blue-50 border-b last:border-0">

                            <p class="text-sm font-semibold text-gray-900" x-text="c.name"></p>
                            <p class="text-xs text-gray-500" x-text="c.phone"></p>

                        </button>
                    </template>

                </div>

                <button type="button"
                        @click="clientMode='new'"
                        class="text-sm text-blue-600 font-semibold">
                    + Crear nuevo cliente
                </button>
            </div>

            {{-- CLIENTE SELECCIONADO --}}
            <div x-show="clientMode === 'selected'"
                 class="bg-blue-50 rounded-2xl p-4 flex justify-between items-center">

                <input type="hidden" name="client_id" :value="selectedClient?.id">

                <div>
                    <p class="font-bold text-blue-800" x-text="selectedClient?.name"></p>
                    <p class="text-xs text-blue-600" x-text="selectedClient?.phone"></p>
                </div>

                <button type="button"
                        @click="resetClient()"
                        class="text-xs text-gray-500 underline">
                    Cambiar
                </button>
            </div>

            {{-- CLIENTE NUEVO --}}
            <div x-show="clientMode === 'new'" class="space-y-3">

                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="client_first_name" placeholder="Nombre"
                           class="input-clean">

                    <input type="text" name="client_last_name" placeholder="Apellido"
                           class="input-clean">
                </div>

                <input type="text" name="client_phone" placeholder="Teléfono"
                       class="input-clean">

                <button type="button"
                        @click="resetClient()"
                        class="text-sm text-gray-500">
                    Cancelar
                </button>
            </div>

        </div>

        {{-- ================= PRODUCTO ================= --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 space-y-3">

            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Producto</h2>

            <x-searchable-select
                name="product_id"
                placeholder="Seleccionar producto..."
                :options="$products->map(fn($p) => [
                    'value' => (string)$p->id,
                    'label' => $p->name . ' — $' . number_format($p->base_price, 0, ',', '.')
                ])->toArray()"
                @selected.window="selectProduct($event.detail.value)"
            />

        </div>

        {{-- ================= DETALLES ================= --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 space-y-4">

            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Detalles</h2>

            <x-searchable-select
                name="color"
                placeholder="Color"
                :options="[
                    ['value'=>'Negro','label'=>'Negro'],
                    ['value'=>'Gris','label'=>'Gris'],
                    ['value'=>'Beige','label'=>'Beige'],
                ]"
            />

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" x-model="sticker" name="sticker">
                Incluye calcomanía
            </label>

            <div x-show="sticker">
                <x-searchable-select
                    name="sticker_color"
                    placeholder="Color de calcomanía"
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

            <input type="date" name="due_date"
                   class="input-clean">

            <textarea name="observations"
                      placeholder="Observaciones..."
                      class="input-clean"></textarea>

        </div>

        {{-- ================= PAGO ================= --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 space-y-4">

            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pago</h2>

            <input type="number"
                   name="price"
                   x-model="price"
                   placeholder="Precio total"
                   class="input-clean text-blue-700 font-bold text-lg">

            <input type="number"
                   name="advance_payment"
                   x-model="advance"
                   placeholder="Anticipo"
                   class="input-clean">

            <div class="flex justify-between text-sm bg-gray-50 p-3 rounded-xl">
                <span class="text-gray-500">Saldo</span>

                <span class="font-bold text-red-600"
                      x-text="'$' + ((price||0) - (advance||0)).toLocaleString('es-CO')">
                </span>
            </div>

        </div>

        {{-- BUTTON --}}
        <button type="submit"
                class="w-full bg-blue-700 text-white font-black py-4 rounded-2xl shadow-md active:scale-95">
            Crear orden
        </button>

    </form>
</div>
</x-app-layout>