@props([
    'label' => null,
    'name',
    'options' => [],
    'value' => '',
    'placeholder' => 'Pilih...',
    'required' => false,
    'searchable' => true,
    'clearable' => false
])

<div x-data="combobox({
        name: '{{ $name }}',
        options: {{ json_encode($options) }},
        initialValue: '{{ old($name, $value) }}',
        placeholder: '{{ $placeholder }}',
        searchable: {{ $searchable ? 'true' : 'false' }},
        clearable: {{ $clearable ? 'true' : 'false' }}
    })" x-init="init()" class="relative">
    
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <!-- Hidden Input -->
    <input type="hidden" name="{{ $name }}" x-model="selectedValue" @if($required) required @endif>

    <!-- Dropdown Button -->
    <div class="relative">
        <button type="button"
                @click="toggle()"
                @keydown.enter.prevent="toggle()"
                @keydown.space.prevent="toggle()"
                @keydown.arrow-down.prevent="open = true; focusNext()"
                @keydown.arrow-up.prevent="open = true; focusPrev()"
                @keydown.escape="close()"
                class="relative w-full bg-white border border-gray-300 rounded-md pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': hasError }">
            
            <span class="block truncate" x-text="displayText" :class="{ 'text-gray-400': !selectedValue }"></span>
            
            <!-- Dropdown Arrow -->
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                     :class="{ 'rotate-180': open }" 
                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </span>

            <!-- Clear Button -->
            <template x-if="clearable && selectedValue">
                <button type="button"
                        @click.stop="clear()"
                        class="absolute inset-y-0 right-8 flex items-center pr-1">
                    <svg class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </template>
        </button>

        <!-- Dropdown Panel -->
        <div x-show="open"
             @click.away="close()"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
            
            <!-- Search Input -->
            <template x-if="searchable">
                <div class="sticky top-0 bg-white border-b border-gray-200 px-3 py-2">
                    <input type="text"
                           x-model="searchQuery"
                           @keydown.enter.prevent="selectFirst()"
                           @keydown.arrow-down.prevent="focusNext()"
                           @keydown.arrow-up.prevent="focusPrev()"
                           @keydown.escape="close()"
                           placeholder="Cari..."
                           class="w-full border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </template>

            <!-- Options List -->
            <ul class="py-1">
                <template x-for="(option, index) in filteredOptions" :key="option.value">
                    <li>
                        <button type="button"
                                @click="select(option.value)"
                                @keydown.enter.prevent="select(option.value)"
                                @keydown.space.prevent="select(option.value)"
                                @keydown.arrow-down.prevent="focusNext()"
                                @keydown.arrow-up.prevent="focusPrev()"
                                @keydown.escape="close()"
                                :class="{
                                    'bg-indigo-600 text-white': selectedValue == option.value,
                                    'text-gray-900 hover:bg-gray-100': selectedValue != option.value
                                }"
                                class="w-full text-left px-3 py-2 text-sm cursor-pointer focus:outline-none focus:bg-indigo-100">
                            <span x-text="option.label"></span>
                            <template x-if="selectedValue == option.value">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-4">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </template>
                        </button>
                    </li>
                </template>
                
                <!-- No Results -->
                <template x-if="filteredOptions.length === 0 && searchQuery">
                    <li class="px-3 py-2 text-sm text-gray-500">
                        Tidak ada hasil ditemukan
                    </li>
                </template>
            </ul>
        </div>
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <script>
        function combobox(config) {
            return {
                name: config.name,
                options: [],
                filteredOptions: [],
                selectedValue: config.initialValue || '',
                displayText: config.placeholder,
                searchQuery: '',
                open: false,
                hasError: false,
                searchable: config.searchable,
                clearable: config.clearable,
                placeholder: config.placeholder,

                init() {
                    // Convert options object to array format
                    this.options = Object.entries(config.options).map(([value, label]) => ({
                        value: value,
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
                    
                    this.filteredOptions = this.options.filter(option =>
                        option.label.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                },

                toggle() {
                    this.open = !this.open;
                    if (this.open) {
                        this.searchQuery = '';
                        this.filterOptions();
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
                },

                clear() {
                    this.selectedValue = '';
                    this.updateDisplayText();
                },

                selectFirst() {
                    if (this.filteredOptions.length > 0) {
                        this.select(this.filteredOptions[0].value);
                    }
                },

                focusNext() {
                    // Implementation for keyboard navigation
                },

                focusPrev() {
                    // Implementation for keyboard navigation
                }
            }
        }
    </script>
</div>
