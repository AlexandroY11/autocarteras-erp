<x-app-layout title="Registrar Compra">
<div class="pt-4">
    <div class="flex items-center gap-3 mb-6">
        <a href="/material-purchases" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-black text-gray-900">Registrar Compra</h1>
    </div>

    <form method="POST" action="/material-purchases"
        class="space-y-4"
        x-data="{
            materialMode: 'select',
            supplierMode: 'select',
            materialId: '',
            supplierId: '',
            quantity: '',
            unitPrice: '',
            get total() {
                return (parseFloat(this.quantity) || 0) * (parseFloat(this.unitPrice) || 0);
            },
            materials: {{ $materials->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'unit' => $m->unit, 'supplier_id' => $m->supplier_id])->toJson() }},
            suppliers: {{ $suppliers->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->toJson() }},
            get selectedMaterial() {
                return this.materials.find(m => m.id == this.materialId);
            }
        }">
        @csrf

        {{-- ── MATERIAL ── --}}
        <div class="bg-white card p-4 space-y-3">
            <div class="flex justify-between items-center">
                <p class="section-title">Material</p>
                <div class="flex gap-2">
                    <button type="button"
                        @click="materialMode = 'select'"
                        :class="materialMode === 'select' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600'"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all">
                        Existente
                    </button>
                    <button type="button"
                        @click="materialMode = 'new'; materialId = ''"
                        :class="materialMode === 'new' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600'"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all">
                        + Nuevo
                    </button>
                </div>
            </div>

            {{-- Seleccionar existente --}}
            <div x-show="materialMode === 'select'">
                <x-searchable-select
                    name="material_id"
                    placeholder="Buscar material..."
                    :options="$materials->map(fn($m) => ['value' => (string)$m->id, 'label' => $m->name . ' (' . $m->unit . ')'])->toArray()"
                />
            </div>

            {{-- Crear nuevo --}}
            <div x-show="materialMode === 'new'" class="space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre *</label>
                    <input type="text" name="new_material_name"
                        placeholder="Ej: Resina, Pintura negra..."
                        class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Unidad *</label>
                    <div class="grid grid-cols-3 gap-2" x-data="{ unit: '' }">
                        <input type="hidden" name="new_material_unit" :value="unit">
                        @foreach(['kg' => 'kg', 'g' => 'g', 'lt' => 'lt', 'ml' => 'ml', 'unidad' => 'Und'] as $val => $label)
                        <button type="button"
                            @click="unit = '{{ $val }}'"
                            :class="unit === '{{ $val }}' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600'"
                            class="border-2 rounded-xl py-2.5 text-sm font-bold transition-all">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PROVEEDOR ── --}}
        <div class="bg-white card p-4 space-y-3">
            <div class="flex justify-between items-center">
                <p class="section-title">Proveedor</p>
                <div class="flex gap-2">
                    <button type="button"
                        @click="supplierMode = 'select'"
                        :class="supplierMode === 'select' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600'"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all">
                        Existente
                    </button>
                    <button type="button"
                        @click="supplierMode = 'new'; supplierId = ''"
                        :class="supplierMode === 'new' ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600'"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all">
                        + Nuevo
                    </button>
                    <button type="button"
                        @click="supplierMode = 'none'; supplierId = ''"
                        :class="supplierMode === 'none' ? 'bg-gray-700 text-white' : 'bg-gray-100 text-gray-600'"
                        class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all">
                        Ninguno
                    </button>
                </div>
            </div>

            <div x-show="supplierMode === 'select'">
                <x-searchable-select
                    name="supplier_id"
                    placeholder="Buscar proveedor..."
                    :options="$suppliers->map(fn($s) => ['value' => (string)$s->id, 'label' => $s->name])->toArray()"
                />
            </div>

            <div x-show="supplierMode === 'new'" class="space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre empresa *</label>
                    <input type="text" name="new_supplier_name"
                        placeholder="Ej: Distribuidora XYZ"
                        class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Teléfono</label>
                    <input type="text" name="new_supplier_phone"
                        placeholder="Opcional"
                        class="input-field">
                </div>
            </div>

            <div x-show="supplierMode === 'none'" class="text-sm text-gray-400 py-2">
                La compra se registrará sin proveedor.
            </div>
        </div>

        {{-- ── CANTIDAD Y PRECIO ── --}}
        <div class="bg-white card p-4 space-y-4">
            <p class="section-title">Detalle de compra</p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Cantidad *</label>
                    <input type="number" name="quantity" x-model="quantity"
                        min="0.01" step="0.01" required
                        class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Precio / unidad *</label>
                    <input type="number" name="unit_price" x-model="unitPrice"
                        min="0" step="100" required
                        class="input-field">
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex justify-between items-center">
                <span class="text-sm font-bold text-blue-700">Total</span>
                <span class="text-3xl font-black text-blue-700"
                    x-text="'$' + total.toLocaleString('es-CO', {maximumFractionDigits: 0})">
                    $0
                </span>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de compra *</label>
                <input type="date" name="purchased_at" value="{{ now()->toDateString() }}" required
                    class="input-field">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Notas</label>
                <textarea name="notes" rows="2" placeholder="Observaciones opcionales..."
                    class="input-field"></textarea>
            </div>
        </div>

        <button type="submit" class="btn-primary">
            Registrar compra
        </button>
    </form>
</div>
</x-app-layout>