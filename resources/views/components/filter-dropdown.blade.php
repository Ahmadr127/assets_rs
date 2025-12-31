@props([
    'name',
    'label' => 'Pilih...',
    'options' => [],
    'value' => ''
])

@php
    $selectedValue = old($name, $value ?? request($name));
    $selectedLabel = $selectedValue && isset($options[$selectedValue]) ? $options[$selectedValue] : null;
@endphp

<div 
    x-data="filterDropdown({
        name: '{{ $name }}',
        options: {{ Js::from($options) }},
        initialValue: '{{ $selectedValue }}',
        placeholder: '{{ $label }}'
    })"
    x-init="init()"
    class="relative"
>
    <!-- Dropdown Button -->
    <button 
        type="button"
        @click="toggle()"
        @keydown.escape="close()"
        @keydown.arrow-down.prevent="open = true"
        class="relative w-full bg-white border border-gray-300 rounded pl-2 pr-8 py-1.5 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-xs"
    >
        <span 
            class="block truncate" 
            x-text="displayText" 
            :class="{ 'text-gray-400': !selectedValue, 'text-gray-900': selectedValue }"
        ></span>
        
        <!-- Dropdown Arrow -->
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg 
                class="h-4 w-4 text-gray-400 transition-transform duration-200" 
                :class="{ 'rotate-180': open }" 
                xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 20 20" 
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </span>
    </button>

    <!-- Dropdown Panel -->
    <div 
        x-show="open"
        x-cloak
        @click.away="close()"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded border border-gray-200 overflow-hidden focus:outline-none"
    >
        <!-- Search Input -->
        <div class="sticky top-0 bg-white border-b border-gray-200 p-2">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-xs"></i>
                </div>
                <input 
                    type="text"
                    x-model="searchQuery"
                    x-ref="searchInput"
                    @keydown.enter.prevent="selectFirst()"
                    @keydown.escape="close()"
                    placeholder="Cari..."
                    class="w-full pl-7 pr-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>
        </div>

        <!-- Options List -->
        <ul class="py-1 max-h-48 overflow-auto">
            <!-- Default/All Option -->
            <li>
                <button 
                    type="button"
                    @click="select('')"
                    class="w-full text-left px-3 py-1.5 text-xs cursor-pointer focus:outline-none"
                    :class="{
                        'bg-indigo-600 text-white': !selectedValue,
                        'text-gray-600 hover:bg-gray-100': selectedValue
                    }"
                >
                    <span x-text="placeholder"></span>
                </button>
            </li>
            
            <!-- Filtered Options -->
            <template x-for="option in filteredOptions" :key="option.value">
                <li>
                    <button 
                        type="button"
                        @click="select(option.value)"
                        class="w-full text-left px-3 py-1.5 text-xs cursor-pointer focus:outline-none flex items-center justify-between"
                        :class="{
                            'bg-indigo-600 text-white': selectedValue == option.value,
                            'text-gray-900 hover:bg-gray-100': selectedValue != option.value
                        }"
                    >
                        <span x-text="option.label"></span>
                        <template x-if="selectedValue == option.value">
                            <i class="fas fa-check text-xs"></i>
                        </template>
                    </button>
                </li>
            </template>
            
            <!-- No Results -->
            <template x-if="filteredOptions.length === 0 && searchQuery">
                <li class="px-3 py-2 text-xs text-gray-500 text-center">
                    Tidak ditemukan
                </li>
            </template>
        </ul>
    </div>
</div>

<script>
    function filterDropdown(config) {
        return {
            name: config.name,
            options: [],
            filteredOptions: [],
            selectedValue: config.initialValue || '',
            displayText: config.placeholder,
            searchQuery: '',
            open: false,
            placeholder: config.placeholder,

            init() {
                // Convert options object to array format
                this.options = Object.entries(config.options).map(([value, label]) => ({
                    value: String(value),
                    label: label
                }));
                
                this.filteredOptions = this.options;
                this.updateDisplayText();
                
                // Watch for search query changes
                this.$watch('searchQuery', () => {
                    this.filterOptions();
                });
            },

            updateDisplayText() {
                if (this.selectedValue) {
                    const option = this.options.find(opt => opt.value == this.selectedValue);
                    this.displayText = option ? option.label : this.placeholder;
                } else {
                    this.displayText = this.placeholder;
                }
            },

            filterOptions() {
                if (!this.searchQuery) {
                    this.filteredOptions = this.options;
                    return;
                }
                
                const query = this.searchQuery.toLowerCase();
                this.filteredOptions = this.options.filter(option =>
                    option.label.toLowerCase().includes(query)
                );
            },

            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.searchQuery = '';
                    this.filterOptions();
                    this.$nextTick(() => {
                        this.$refs.searchInput?.focus();
                    });
                }
            },

            close() {
                this.open = false;
                this.searchQuery = '';
            },

            select(value) {
                this.selectedValue = value;
                this.updateDisplayText();
                this.close();
                this.applyFilter();
            },

            selectFirst() {
                if (this.filteredOptions.length > 0) {
                    this.select(this.filteredOptions[0].value);
                }
            },

            applyFilter() {
                const url = new URL(window.location);
                if (this.selectedValue) {
                    url.searchParams.set(this.name, this.selectedValue);
                } else {
                    url.searchParams.delete(this.name);
                }
                // Reset to page 1 when filter changes
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }
        }
    }
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
