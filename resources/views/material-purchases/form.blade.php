<x-app-layout title="Registrar Compra">
<div class="pt-4">
    <div class="flex items-center gap-3 mb-6">
        <a href="/material-purchases" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-black text-gray-900">Registrar Compra</h1>
    </div>

    <form method="POST" action="/material-purchases"
        class="space-y-4"
        x-data="{
            materialId: '',
            quantity: '',
            unitPrice: '',
            get total() {
                const q = parseFloat(this.quantity) || 0;
                const p = parseFloat(this.unitPrice) || 0;
                return q * p;
            },
            materials: {{ $materials->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'unit' => $m->unit, 'supplier_id' => $m->supplier_id])->toJson() }},
            get selectedMaterial() {
                return this.materials.find(m => m.id == this.materialId);
            }
        }">
        @csrf

        <div class="bg-white card p-4 space-y-4">

            {{-- Material --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Material *</label>
                <select name="material_id" x-model="materialId" required class="input-field">
                    <option value="">Seleccionar material...</option>
                    @foreach($materials as $material)
                    <option value="{{ $material->id }}">{{ $material->name }} ({{ $material->unit }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Proveedor --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Proveedor</label>
                <select name="supplier_id" class="input-field">
                    <option value="">Sin proveedor</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Cantidad y precio --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Cantidad
                        <span x-show="selectedMaterial" x-text="selectedMaterial ? '(' + selectedMaterial.unit + ')' : ''" class="text-gray-400 font-normal"></span>
                    </label>
                    <input type="number" name="quantity" x-model="quantity"
                        min="0.01" step="0.01" required
                        class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Precio por unidad *</label>
                    <input type="number" name="unit_price" x-model="unitPrice"
                        min="0" step="100" required
                        class="input-field">
                </div>
            </div>

            {{-- Total calculado --}}
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex justify-between items-center">
                <span class="text-sm font-semibold text-green-700">Total a pagar</span>
                <span class="text-2xl font-black text-green-700"
                    x-text="'$' + total.toLocaleString('es-CO', {maximumFractionDigits: 0})">
                    $0
                </span>
            </div>

            {{-- Fecha --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de compra *</label>
                <input type="date" name="purchased_at" value="{{ now()->toDateString() }}" required
                    class="input-field">
            </div>

            {{-- Notas --}}
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