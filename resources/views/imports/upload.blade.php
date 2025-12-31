@extends('layouts.app')

@section('title', 'Upload File Import')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Upload File Import</h1>
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

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Upload File Excel</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('imports.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-6">
                        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel</label>
                        <input type="file" 
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500 @error('file') border-red-500 @enderror" 
                               id="file" 
                               name="file" 
                               accept=".xlsx,.xls,.csv" 
                               required>
                        @error('file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            Format yang didukung: .xlsx, .xls, .csv (Maksimal 10MB)
                        </p>
                    </div>

                    <div class="mb-6">
                        <label for="entity_type" class="block text-sm font-medium text-gray-700 mb-2">Tipe Entity</label>
                        <select class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                id="entity_type" 
                                name="entity_type">
                            <option value="fixed_assets">Fixed Assets</option>
                        </select>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mr-3 mt-0.5"></i>
                            <div>
                                <h3 class="text-sm font-semibold text-blue-900 mb-2">Panduan Import</h3>
                                <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                                    <li>Pastikan file Excel memiliki header di baris pertama</li>
                                    <li>Semua kolom bersifat opsional - isi sesuai kebutuhan</li>
                                    <li>Kode akan di-generate otomatis jika tidak diisi (Format: FA20250105-0001)</li>
                                    <li>Format tanggal: YYYY-MM-DD atau DD/MM/YYYY</li>
                                    <li>Taksiran umur dalam satuan tahun (angka)</li>
                                    <li>Sistem akan mendeteksi duplikasi berdasarkan Kode atau Nama+Lokasi</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-upload mr-2"></i>Upload dan Lanjutkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sample Template -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Template Excel</h2>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Download template Excel untuk memudahkan proses import:</p>
                <a href="{{ asset('templates/fixed_assets_import_template.xlsx') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors" 
                   download>
                    <i class="fas fa-download mr-2"></i>Download Template
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileSize = file.size / 1024 / 1024; // in MB
        if (fileSize > 10) {
            alert('Ukuran file melebihi 10MB. Silakan pilih file yang lebih kecil.');
            e.target.value = '';
        }
    }
});
</script>
@endpush
@endsection
