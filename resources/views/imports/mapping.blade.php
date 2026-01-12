@extends('layouts.app')

@section('title', 'Mapping Kolom Import')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Mapping Kolom Import</h1>
        <a href="{{ route('imports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                <ul class="text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">
                File: {{ $batch->original_filename }}
            </h2>
        </div>
        <div class="p-6">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mr-3 mt-0.5"></i>
                    <p class="text-sm text-blue-800">
                        Petakan kolom dari file Excel ke field database. Kolom yang tidak diperlukan dapat di-skip.
                    </p>
                </div>
            </div>

            <form action="{{ route('imports.configure-mapping', $batch->id) }}" method="POST">
                @csrf
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">Kolom Excel</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Field Database</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Preview Data</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($headers as $index => $header)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="font-semibold text-gray-900">{{ $header['excel_column'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $header['excel_header'] }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div x-data="searchableMapping({
                                            name: 'mapping[{{ $header['excel_column'] }}]',
                                            options: {{ json_encode(array_merge(['skip' => '-- Skip --'], $availableFields)) }},
                                            initialValue: '{{ $header['suggested_field'] ?? 'skip' }}',
                                            placeholder: '-- Skip --',
                                            isSuggested: {{ $header['suggested_field'] ? 'true' : 'false' }}
                                        })" x-init="init()" class="relative">
                                            
                                            <!-- Hidden Input -->
                                            <input type="hidden" :name="name" x-model="selectedValue">

                                            <!-- Dropdown Button -->
                                            <div class="relative">
                                                <button type="button"
                                                        @click="toggle()"
                                                        @keydown.escape="close()"
                                                        class="relative w-full bg-white border rounded-lg pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                                        :class="isSuggested && selectedValue !== 'skip' ? 'border-green-500' : 'border-gray-300'">
                                                    
                                                    <span class="block truncate" x-text="displayText" :class="{ 'text-gray-400': selectedValue === 'skip' }"></span>
                                                    
                                                    <!-- Dropdown Arrow -->
                                                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                                                             :class="{ 'rotate-180': open }" 
                                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
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
                                                    <div class="sticky top-0 bg-white border-b border-gray-200 px-3 py-3">
                                                        <input type="text"
                                                               x-model="searchQuery"
                                                               x-ref="searchInput"
                                                               @keydown.enter.prevent="selectFirst()"
                                                               @keydown.escape="close()"
                                                               placeholder="Cari field..."
                                                               class="w-full px-3 py-2.5 border-gray-300 rounded-md text-base focus:ring-blue-500 focus:border-blue-500">
                                                    </div>

                                                    <!-- Options List -->
                                                    <ul class="py-1">
                                                        <template x-for="option in filteredOptions" :key="option.value">
                                                            <li>
                                                                <button type="button"
                                                                        @click="select(option.value)"
                                                                        :class="{
                                                                            'bg-blue-600 text-white': selectedValue == option.value,
                                                                            'text-gray-900 hover:bg-gray-100': selectedValue != option.value
                                                                        }"
                                                                        class="w-full text-left px-3 py-2 text-sm cursor-pointer focus:outline-none focus:bg-blue-100">
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
                                        </div>
                                        @if($header['suggested_field'])
                                            <div class="mt-1 flex items-center text-xs text-green-600">
                                                <i class="fas fa-check-circle mr-1"></i> Auto-detected
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 italic">
                                        Preview akan ditampilkan setelah mapping
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mt-6">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mr-3 mt-0.5"></i>
                        <div>
                            <h3 class="text-sm font-semibold text-blue-900 mb-2">Informasi Penting:</h3>
                            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                                <li><strong>Kode:</strong> Akan di-generate otomatis jika tidak diisi (Format: FA20250105-0001)</li>
                                <li><strong>Semua field bersifat opsional</strong> - isi sesuai kebutuhan</li>
                                <li><strong>Duplikasi:</strong> Sistem akan cek berdasarkan Kode atau kombinasi Nama+Lokasi</li>
                                <li><strong>Master Data:</strong> Lokasi, Status, Kondisi, Vendor, Brand akan dibuat otomatis jika belum ada</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    <a href="{{ route('imports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Lanjut ke Preview<i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Searchable Mapping Dropdown Function
function searchableMapping(config) {
    return {
        name: config.name,
        options: [],
        filteredOptions: [],
        selectedValue: config.initialValue || 'skip',
        displayText: config.placeholder,
        searchQuery: '',
        open: false,
        isSuggested: config.isSuggested,
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

            // Watch for open state to focus search input
            this.$watch('open', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        if (this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                        }
                    });
                }
            });
        },

        updateDisplayText() {
            if (this.selectedValue && this.selectedValue !== 'skip') {
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
                option.label.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                option.value.toLowerCase().includes(this.searchQuery.toLowerCase())
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

        selectFirst() {
            if (this.filteredOptions.length > 0) {
                this.select(this.filteredOptions[0].value);
            }
        }
    }
}
</script>
@endpush
@endsection
