@extends('layouts.app')

@section('title', 'Progress Import')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Progress Import</h1>
        <a href="{{ route('imports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $batch->original_filename }}
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <!-- Status Badge -->
                <div class="text-center">
                    @php
                        $statusConfig = match($batch->status) {
                            'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check-circle'],
                            'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times-circle'],
                            'processing', 'validating' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-spinner fa-spin'],
                            'cancelled' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-ban'],
                            default => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-clock']
                        };
                    @endphp
                    <div class="inline-flex items-center px-6 py-3 {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} rounded-lg text-lg font-semibold">
                        <i class="fas {{ $statusConfig['icon'] }} mr-3"></i>
                        {{ strtoupper($batch->status) }}
                    </div>
                </div>

                <!-- Progress Bar -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-sm font-medium text-gray-700">Progress</h3>
                        <span id="progressText" class="text-sm font-semibold text-gray-900">{{ $statistics['progress_percentage'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-8">
                        <div id="progressBar" 
                             class="bg-blue-600 h-8 rounded-full flex items-center justify-center text-sm text-white font-semibold transition-all duration-500" 
                             style="width: {{ $statistics['progress_percentage'] }}%">
                            <span id="progressBarText">{{ $statistics['progress_percentage'] }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 rounded-lg p-4 text-center border border-blue-200">
                        <i class="fas fa-file-excel text-blue-600 text-2xl mb-2"></i>
                        <div id="totalRows" class="text-2xl font-bold text-gray-900">{{ number_format($statistics['total_rows']) }}</div>
                        <div class="text-xs text-gray-600 mt-1">Total Rows</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center border border-green-200">
                        <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
                        <div id="successRows" class="text-2xl font-bold text-gray-900">{{ number_format($statistics['success_rows']) }}</div>
                        <div class="text-xs text-gray-600 mt-1">Success</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 text-center border border-red-200">
                        <i class="fas fa-times-circle text-red-600 text-2xl mb-2"></i>
                        <div id="failedRows" class="text-2xl font-bold text-gray-900">{{ number_format($statistics['failed_rows']) }}</div>
                        <div class="text-xs text-gray-600 mt-1">Failed</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 text-center border border-yellow-200">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mb-2"></i>
                        <div id="duplicateRows" class="text-2xl font-bold text-gray-900">{{ number_format($statistics['duplicate_rows']) }}</div>
                        <div class="text-xs text-gray-600 mt-1">Duplicates</div>
                    </div>
                </div>

                <!-- Time Information -->
                @if($statistics['started_at'])
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-semibold text-blue-900">Started:</span>
                                <span class="text-blue-800">{{ $statistics['started_at'] }}</span>
                            </div>
                            @if($statistics['completed_at'])
                                <div>
                                    <span class="font-semibold text-blue-900">Completed:</span>
                                    <span class="text-blue-800">{{ $statistics['completed_at'] }}</span>
                                </div>
                            @endif
                        </div>
                        @if($statistics['duration'])
                            <div class="mt-2 text-sm">
                                <span class="font-semibold text-blue-900">Duration:</span>
                                <span class="text-blue-800">{{ $statistics['duration'] }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="space-y-3">
                    @if($batch->isCompleted())
                        <a href="{{ route('imports.show', $batch->id) }}" 
                           class="block w-full text-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                            <i class="fas fa-eye mr-2"></i>Lihat Detail Hasil
                        </a>
                    @elseif($batch->isFailed())
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                                <p class="text-sm text-red-800">
                                    Import gagal. Silakan cek log error atau coba lagi.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('imports.show', $batch->id) }}" 
                           class="block w-full text-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                            <i class="fas fa-eye mr-2"></i>Lihat Error Details
                        </a>
                        <form action="{{ route('imports.retry', $batch->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors">
                                <i class="fas fa-redo mr-2"></i>Retry Import
                            </button>
                        </form>
                    @else
                        <button class="w-full px-6 py-3 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed" disabled>
                            <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let batchId = {{ $batch->id }};
let refreshInterval;

function updateProgress() {
    fetch(`/imports/${batchId}/progress-data`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const stats = data.data;
                
                // Update progress bar
                const progressBar = document.getElementById('progressBar');
                const progressText = document.getElementById('progressText');
                const progressBarText = document.getElementById('progressBarText');
                
                progressBar.style.width = stats.progress_percentage + '%';
                progressText.textContent = stats.progress_percentage + '%';
                progressBarText.textContent = stats.progress_percentage + '%';
                
                // Update statistics
                document.getElementById('totalRows').textContent = stats.total_rows.toLocaleString();
                document.getElementById('successRows').textContent = stats.success_rows.toLocaleString();
                document.getElementById('failedRows').textContent = stats.failed_rows.toLocaleString();
                document.getElementById('duplicateRows').textContent = stats.duplicate_rows.toLocaleString();
                
                // Check if completed or failed
                if (stats.status === 'completed' || stats.status === 'failed') {
                    clearInterval(refreshInterval);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                }
            }
        })
        .catch(error => {
            console.error('Error fetching progress:', error);
        });
}

// Auto-refresh every 3 seconds if processing
@if($batch->isProcessing())
    refreshInterval = setInterval(updateProgress, 3000);
@endif
</script>
@endpush
@endsection
