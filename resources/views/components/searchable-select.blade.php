@props([
    'name',
    'options' => [],
    'placeholder' => 'Buscar...',
    'selected' => '',
    'disabled' => false,
])

<div x-data="{
    open: false,
    search: '',
    selected: '{{ $selected }}',
    selectedLabel: '',
    options: {{ json_encode($options) }},

    get filtered() {
        if (!this.search) return this.options;
        const q = this.search.toLowerCase();
        return this.options.filter(o => o.label.toLowerCase().includes(q));
    },

    select(option) {
        this.selected = option.value;
        this.selectedLabel = option.label;
        this.search = '';
        this.open = false;
        this.$dispatch('selected', { value: option.value });
    },

    init() {
        this.updateLabel();
        // Esto hace que si las opciones cambian externamente, el componente se actualice
        this.$watch('options', () => this.updateLabel());
    },

    updateLabel() {
        const found = this.options.find(o => o.value == this.selected);
        if (found) {
            this.selectedLabel = found.label;
        } else {
            this.selectedLabel = '';
            this.selected = '';
        }
    }
}"  class="relative" @click.outside="open = false">

    <input type="hidden" name="{{ $name }}" :value="selected">

    <button type="button"
        @click="open = !open"
        :disabled="{{ $disabled ? 'true' : 'false' }}"
        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm text-left focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100 flex justify-between items-center">
        <span :class="selectedLabel ? 'text-gray-800' : 'text-gray-400'"
            x-text="selectedLabel || '{{ $placeholder }}'"></span>
        <span class="text-gray-400">▾</span>
    </button>

    <div x-show="open" x-transition
        class="absolute z-50 w-full bg-white border border-gray-200 rounded-xl shadow-lg mt-1 overflow-hidden">
        <div class="p-2 border-b border-gray-100">
            <input type="text"
                x-model="search"
                @click.stop
                placeholder="Buscar..."
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                x-ref="searchInput"
                x-init="$watch('open', val => val && $nextTick(() => $refs.searchInput.focus()))">
        </div>
        <div class="max-h-48 overflow-y-auto">
            <template x-for="option in filtered" :key="option.value">
                <button type="button"
                    @click="select(option)"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-blue-50 border-b border-gray-50 last:border-0"
                    :class="selected == option.value ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700'"
                    x-text="option.label">
                </button>
            </template>
            <div x-show="filtered.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">
                Sin resultados
            </div>
        </div>
    </div>
</div>