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
                                        <select name="mapping[{{ $header['excel_column'] }}]" 
                                                class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ $header['suggested_field'] ? 'border-green-500' : '' }}">
                                            <option value="">-- Skip --</option>
                                            @foreach($availableFields as $field => $label)
                                                <option value="{{ $field }}" 
                                                    {{ $header['suggested_field'] === $field ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
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
// Highlight mapped fields
document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('select[name^="mapping"]');
    
    selects.forEach(select => {
        select.addEventListener('change', function() {
            if (this.value && this.value !== '') {
                this.classList.add('border-blue-500', 'ring-2', 'ring-blue-200');
            } else {
                this.classList.remove('border-blue-500', 'ring-2', 'ring-blue-200');
            }
        });
        
        // Initial check
        if (select.value && select.value !== '') {
            select.classList.add('border-blue-500', 'ring-2', 'ring-blue-200');
        }
    });
});
</script>
@endpush
@endsection
