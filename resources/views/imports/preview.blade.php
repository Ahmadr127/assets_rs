@extends('layouts.app')

@section('title', 'Preview Data Import')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Preview Data Import</h1>
        <a href="{{ route('imports.mapping', $batch->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Mapping
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

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">Total Rows</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_rows']) }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-file-excel text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-green-600 uppercase tracking-wider mb-1">Valid Data</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['valid_rows']) }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wider mb-1">Duplicates</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['duplicate_rows']) }}</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-1">Errors</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($summary['error_rows']) }}</p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Rate -->
    <div class="bg-white shadow-sm rounded-lg p-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Success Rate</h3>
        <div class="w-full bg-gray-200 rounded-full h-8">
            <div class="bg-green-600 h-8 rounded-full flex items-center justify-center text-sm text-white font-semibold transition-all duration-500" 
                 style="width: {{ $summary['success_rate'] }}%">
                {{ $summary['success_rate'] }}% Valid
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div x-data="{ activeTab: 'valid' }" class="bg-white shadow-sm rounded-lg">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'valid'" 
                        :class="activeTab === 'valid' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center px-6 py-4 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-check-circle mr-2"></i>
                    Valid Data ({{ count($validatedData['valid']) }})
                </button>
                <button @click="activeTab = 'duplicates'" 
                        :class="activeTab === 'duplicates' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center px-6 py-4 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Duplicates ({{ count($validatedData['duplicates']) }})
                </button>
                <button @click="activeTab = 'errors'" 
                        :class="activeTab === 'errors' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center px-6 py-4 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-times-circle mr-2"></i>
                    Errors ({{ count($validatedData['errors']) }})
                </button>
            </nav>
        </div>

        <!-- Valid Data Tab -->
        <div x-show="activeTab === 'valid'" class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-green-700">Valid Data - Siap Diimport</h3>
                @if(count($validatedData['valid']) > 0)
                    <a href="{{ route('imports.download-filtered', ['batch' => $batch->id, 'type' => 'valid']) }}" 
                       class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Download
                    </a>
                @endif
            </div>
            
            @if(count($validatedData['valid']) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Row</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Umur</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PIC</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(array_slice($validatedData['valid'], 0, 10) as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item['row_index'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item['mapped_data']['kode'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item['mapped_data']['nama_fixed_asset'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $item['mapped_data']['tipe_fixed_asset'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $item['mapped_data']['taksiran_umur'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $item['mapped_data']['lokasi'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $item['mapped_data']['pic'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($validatedData['valid']) > 10)
                    <p class="text-center text-sm text-gray-500 mt-4">
                        Menampilkan 10 dari {{ count($validatedData['valid']) }} data valid
                    </p>
                @endif
            @else
                <p class="text-center text-gray-500 py-8">Tidak ada data valid</p>
            @endif
        </div>

        <!-- Duplicates Tab -->
        <div x-show="activeTab === 'duplicates'" class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-yellow-700">Duplicate Data</h3>
                @if(count($validatedData['duplicates']) > 0)
                    <a href="{{ route('imports.download-filtered', ['batch' => $batch->id, 'type' => 'duplicates']) }}" 
                       class="inline-flex items-center px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Download
                    </a>
                @endif
            </div>
            
            @if(count($validatedData['duplicates']) > 0)
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-500 mr-3 mt-0.5"></i>
                        <p class="text-sm text-yellow-800">
                            Data berikut sudah ada di database. Anda dapat memilih untuk mengupdate atau skip.
                        </p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Row</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duplicate Key</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Existing ID</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(array_slice($validatedData['duplicates'], 0, 10) as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item['row_index'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item['mapped_data']['kode'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item['mapped_data']['nama_fixed_asset'] ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ $item['duplicate_key'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('fixed-assets.show', $item['existing_record_id']) }}" 
                                           target="_blank"
                                           class="text-blue-600 hover:text-blue-800">
                                            #{{ $item['existing_record_id'] }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($validatedData['duplicates']) > 10)
                    <p class="text-center text-sm text-gray-500 mt-4">
                        Menampilkan 10 dari {{ count($validatedData['duplicates']) }} data duplikat
                    </p>
                @endif
            @else
                <p class="text-center text-gray-500 py-8">Tidak ada data duplikat</p>
            @endif
        </div>

        <!-- Errors Tab -->
        <div x-show="activeTab === 'errors'" class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-red-700">Error Data</h3>
                @if(count($validatedData['errors']) > 0)
                    <a href="{{ route('imports.download-filtered', ['batch' => $batch->id, 'type' => 'errors']) }}" 
                       class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-download mr-2"></i>Download
                    </a>
                @endif
            </div>
            
            @if(count($validatedData['errors']) > 0)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-3 mt-0.5"></i>
                        <p class="text-sm text-red-800">
                            Data berikut memiliki error validasi dan tidak akan diimport.
                        </p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Row</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Errors</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(array_slice($validatedData['errors'], 0, 10) as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item['row_index'] }}</td>
                                    <td class="px-4 py-3 text-xs">
                                        @foreach($item['mapped_data'] as $key => $value)
                                            <div class="mb-1">
                                                <span class="font-semibold text-gray-700">{{ $key }}:</span> 
                                                <span class="text-gray-600">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(isset($item['errors']))
                                            <div class="space-y-1">
                                                @foreach($item['errors'] as $field => $messages)
                                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">
                                                        {{ $field }}: {{ is_array($messages) ? implode(', ', $messages) : $messages }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($validatedData['errors']) > 10)
                    <p class="text-center text-sm text-gray-500 mt-4">
                        Menampilkan 10 dari {{ count($validatedData['errors']) }} data error
                    </p>
                @endif
            @else
                <p class="text-center text-gray-500 py-8">Tidak ada data error</p>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    @if(count($validatedData['valid']) > 0)
        <!-- Queue Status Info -->
        @php
            $queueDriver = config('queue.default');
            $isSync = $queueDriver === 'sync';
        @endphp
        
        @if($isSync)
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mr-3 mt-0.5"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">Mode: Synchronous Processing</p>
                        <p>Import akan diproses langsung. Browser akan menunggu hingga proses selesai (~6 menit untuk 2,396 rows).</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-0.5"></i>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold mb-1">Mode: Queue Processing ({{ $queueDriver }})</p>
                        <p>Pastikan queue worker sudah berjalan: <code class="bg-yellow-100 px-2 py-1 rounded">php artisan queue:work</code></p>
                        <p class="mt-1 text-xs">Jika di cPanel/shared hosting, ubah <code class="bg-yellow-100 px-1 rounded">QUEUE_CONNECTION=sync</code> di file .env</p>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pilih Aksi Import</h3>
            <form action="{{ route('imports.process', $batch->id) }}" method="POST" x-data="{ action: 'create' }">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Aksi Import</label>
                    <select name="action" x-model="action" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="create">Create - Buat data baru saja ({{ count($validatedData['valid']) }} data, skip {{ count($validatedData['duplicates']) }} duplikat)</option>
                        <option value="update">Update - Buat data baru + Update duplikat ({{ count($validatedData['valid']) + count($validatedData['duplicates']) }} data total)</option>
                    </select>
                    <p class="mt-2 text-sm text-gray-500" x-show="action === 'create'">
                        <i class="fas fa-info-circle mr-1"></i>
                        Hanya data valid yang akan diimport. Data duplikat akan di-skip.
                    </p>
                    <p class="mt-2 text-sm text-gray-500" x-show="action === 'update'">
                        <i class="fas fa-info-circle mr-1"></i>
                        Data valid akan dibuat baru, data duplikat akan di-update dengan data dari Excel.
                    </p>
                </div>

                <div class="flex justify-between">
                    <a href="{{ route('imports.mapping', $batch->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Mapping
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                        <i class="fas fa-check mr-2"></i>
                        <span x-text="action === 'create' ? 'Proses Import ({{ count($validatedData['valid']) }} data)' : 'Proses Import ({{ count($validatedData['valid']) + count($validatedData['duplicates']) }} data)'"></span>
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3 mt-0.5"></i>
                <p class="text-sm text-yellow-800">
                    Tidak ada data valid untuk diimport. Silakan perbaiki error atau upload file baru.
                </p>
            </div>
        </div>
        <div class="flex justify-between">
            <a href="{{ route('imports.mapping', $batch->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Mapping
            </a>
            <a href="{{ route('imports.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-home mr-2"></i>Kembali ke Daftar Import
            </a>
        </div>
    @endif
</div>
@endsection
