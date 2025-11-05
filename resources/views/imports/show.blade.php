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
                <p class="text-center text-gray-500 py-8">Belum ada log untuk batch ini</p>
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
