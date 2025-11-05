@extends('layouts.app')

@section('title', 'Detail Import Batch')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Detail Import Batch #{{ $batch->id }}</h1>
        <a href="{{ route('imports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Batch Information -->
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Informasi Batch</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Batch ID</span>
                        <span class="text-gray-900">#{{ $batch->id }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Filename</span>
                        <span class="text-gray-900">{{ $batch->original_filename }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Entity Type</span>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $batch->entity_type }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Status</span>
                        @php
                            $statusConfig = match($batch->status) {
                                'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                'processing', 'validating' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                'cancelled' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                default => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800']
                            };
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                            {{ strtoupper($batch->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">User</span>
                        <span class="text-gray-900">{{ $batch->user->name ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Total Rows</span>
                        <span class="text-gray-900">{{ number_format($statistics['total_rows']) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Success</span>
                        <span class="text-green-600 font-semibold">{{ number_format($statistics['success_rows']) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Failed</span>
                        <span class="text-red-600 font-semibold">{{ number_format($statistics['failed_rows']) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Duplicates</span>
                        <span class="text-yellow-600 font-semibold">{{ number_format($statistics['duplicate_rows']) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Success Rate</span>
                        <span class="text-gray-900 font-bold">{{ $statistics['success_rate'] }}%</span>
                    </div>
                </div>
            </div>

            @if($statistics['started_at'])
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="font-semibold text-gray-700">Started:</span>
                            <span class="text-gray-600">{{ $statistics['started_at'] }}</span>
                        </div>
                        @if($statistics['completed_at'])
                            <div>
                                <span class="font-semibold text-gray-700">Completed:</span>
                                <span class="text-gray-600">{{ $statistics['completed_at'] }}</span>
                            </div>
                        @endif
                        @if($statistics['duration'])
                            <div>
                                <span class="font-semibold text-gray-700">Duration:</span>
                                <span class="text-gray-600">{{ $statistics['duration'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Import Summary -->
    @if($batch->import_summary)
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Import Summary</h2>
            </div>
            <div class="p-6">
                <pre class="bg-gray-50 rounded-lg p-4 text-xs overflow-x-auto">{{ json_encode($batch->import_summary, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

    <!-- Import Logs -->
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">Import Logs</h2>
            <div class="flex space-x-2">
                <a href="{{ route('imports.download-filtered', ['batch' => $batch->id, 'type' => 'valid']) }}" 
                   class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-download mr-1"></i>Valid
                </a>
                <a href="{{ route('imports.download-filtered', ['batch' => $batch->id, 'type' => 'duplicates']) }}" 
                   class="inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-download mr-1"></i>Duplicates
                </a>
                <a href="{{ route('imports.download-filtered', ['batch' => $batch->id, 'type' => 'errors']) }}" 
                   class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-download mr-1"></i>Errors
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <!-- Active Filters -->
            @if(request()->hasAny(['status', 'search']))
                <div class="mb-4 flex flex-wrap gap-2 items-center">
                    <span class="text-sm font-medium text-gray-700">Filter aktif:</span>
                    @if(request('status') && request('status') !== 'all')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Status: {{ strtoupper(request('status')) }}
                            <a href="{{ route('imports.show', ['batch' => $batch->id] + request()->except('status')) }}" 
                               class="ml-2 text-blue-600 hover:text-blue-800">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    @endif
                    @if(request('search'))
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Pencarian: "{{ request('search') }}"
                            <a href="{{ route('imports.show', ['batch' => $batch->id] + request()->except('search')) }}" 
                               class="ml-2 text-blue-600 hover:text-blue-800">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    @endif
                </div>
            @endif

            <form method="GET" action="{{ route('imports.show', $batch->id) }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="imported" {{ request('status') === 'imported' ? 'selected' : '' }}>Imported</option>
                            <option value="updated" {{ request('status') === 'updated' ? 'selected' : '' }}>Updated</option>
                            <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error</option>
                            <option value="duplicate" {{ request('status') === 'duplicate' ? 'selected' : '' }}>Duplicate</option>
                            <option value="valid" {{ request('status') === 'valid' ? 'selected' : '' }}>Valid</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <input type="text" name="search" id="search" 
                               value="{{ request('search') }}"
                               placeholder="Cari kode, nama, atau row index..."
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Sort -->
                    <div>
                        <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">Urutkan</label>
                        <select name="sort_by" id="sort_by" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="row_index" {{ request('sort_by', 'row_index') === 'row_index' ? 'selected' : '' }}>Row Index</option>
                            <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>Status</option>
                            <option value="processed_at" {{ request('sort_by') === 'processed_at' ? 'selected' : '' }}>Processed At</option>
                        </select>
                        <input type="hidden" name="sort_order" value="{{ request('sort_order', 'asc') }}">
                    </div>
                </div>

                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        Menampilkan {{ $logs->count() }} dari {{ $logs->total() }} log
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('imports.show', $batch->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-redo mr-2"></i>Reset
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </div>
            </form>

            <!-- Quick Filters -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-sm font-medium text-gray-700">Filter cepat:</span>
                    <a href="{{ route('imports.show', ['batch' => $batch->id, 'status' => 'error']) }}" 
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request('status') === 'error' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Errors ({{ number_format($statistics['failed_rows']) }})
                    </a>
                    <a href="{{ route('imports.show', ['batch' => $batch->id, 'status' => 'duplicate']) }}" 
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request('status') === 'duplicate' ? 'bg-yellow-600 text-white' : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' }}">
                        <i class="fas fa-copy mr-1"></i>
                        Duplicates ({{ number_format($statistics['duplicate_rows']) }})
                    </a>
                    <a href="{{ route('imports.show', ['batch' => $batch->id, 'status' => 'imported']) }}" 
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request('status') === 'imported' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
                        <i class="fas fa-check-circle mr-1"></i>
                        Imported ({{ number_format($statistics['success_rows']) }})
                    </a>
                    <a href="{{ route('imports.show', ['batch' => $batch->id, 'status' => 'updated']) }}" 
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request('status') === 'updated' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800 hover:bg-blue-200' }}">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Updated ({{ number_format($statistics['updated_rows']) }})
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($logs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Row</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Errors/Notes</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Processed At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($logs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $log->row_index }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @php
                                            $logStatusConfig = match($log->status) {
                                                'imported', 'updated' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                                'error' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                                'duplicate' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                                'valid' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                                default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800']
                                            };
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $logStatusConfig['bg'] }} {{ $logStatusConfig['text'] }}">
                                            {{ strtoupper($log->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        @if($log->mapped_data)
                                            <div class="space-y-1">
                                                <div><span class="font-semibold">Kode:</span> {{ $log->mapped_data['kode'] ?? '-' }}</div>
                                                <div><span class="font-semibold">Nama:</span> {{ $log->mapped_data['nama_fixed_asset'] ?? '-' }}</div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        @if($log->errors)
                                            <div class="space-y-1">
                                                @foreach($log->errors as $field => $error)
                                                    <div class="text-red-600">
                                                        <span class="font-semibold">{{ $field }}:</span> 
                                                        {{ is_array($error) ? implode(', ', $error) : $error }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($log->duplicate_key)
                                            <div class="text-yellow-600">
                                                Duplicate: {{ $log->duplicate_key }}
                                                @if($log->existing_record_id)
                                                    (ID: {{ $log->existing_record_id }})
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                        {{ $log->processed_at?->format('d/m/Y H:i:s') ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500 text-lg font-medium mb-2">
                        @if(request()->hasAny(['status', 'search']))
                            Tidak ada log yang sesuai dengan filter
                        @else
                            Belum ada log untuk batch ini
                        @endif
                    </p>
                    @if(request()->hasAny(['status', 'search']))
                        <a href="{{ route('imports.show', $batch->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors mt-2">
                            <i class="fas fa-redo mr-2"></i>Reset Filter
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="flex justify-between items-center">
            <div>
                @if($batch->isFailed())
                    <form action="{{ route('imports.retry', $batch->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-redo mr-2"></i>Retry Import
                        </button>
                    </form>
                @endif
            </div>
            <div>
                @if(!$batch->isProcessing())
                    <form action="{{ route('imports.destroy', $batch->id) }}" method="POST" class="inline"
                          onsubmit="return confirm('Yakin ingin menghapus batch ini? Data yang sudah diimport tidak akan terhapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-trash mr-2"></i>Hapus Batch
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
