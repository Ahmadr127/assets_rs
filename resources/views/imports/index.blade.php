@extends('layouts.app')

@section('title', 'Import Data Fixed Assets')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Import Data Fixed Assets</h1>
        <a href="{{ route('imports.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
            <i class="fas fa-upload mr-2"></i>Upload File Baru
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
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Import</h2>
        </div>
        <div class="p-6">
            @if($batches->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filename</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Rows</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failed</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duplicates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($batches as $batch)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $batch->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-excel text-green-500 mr-2"></i>
                                            <span class="text-gray-900">{{ $batch->original_filename }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusConfig = match($batch->status) {
                                                'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                                'failed' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                                'processing', 'validating' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                                'cancelled' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                                default => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800']
                                            };
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                            {{ strtoupper($batch->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($batch->total_rows) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="text-green-600 flex items-center">
                                            <i class="fas fa-check-circle mr-1"></i> {{ number_format($batch->success_rows) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="text-red-600 flex items-center">
                                            <i class="fas fa-times-circle mr-1"></i> {{ number_format($batch->failed_rows) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="text-yellow-600 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1"></i> {{ number_format($batch->duplicate_rows) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="w-full bg-gray-200 rounded-full h-5">
                                            <div class="bg-blue-600 h-5 rounded-full flex items-center justify-center text-xs text-white font-medium" 
                                                 style="width: {{ $batch->getProgressPercentage() }}%">
                                                {{ $batch->getProgressPercentage() }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $batch->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('imports.show', $batch->id) }}" 
                                               class="text-blue-600 hover:text-blue-900" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($batch->isProcessing())
                                                <a href="{{ route('imports.progress', $batch->id) }}" 
                                                   class="text-blue-600 hover:text-blue-900" title="Progress">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </a>
                                            @endif
                                            
                                            @if($batch->isFailed())
                                                <form action="{{ route('imports.retry', $batch->id) }}" 
                                                      method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900" 
                                                            title="Retry">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if(!$batch->isProcessing())
                                                <form action="{{ route('imports.destroy', $batch->id) }}" 
                                                      method="POST" class="inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus batch ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $batches->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                    <p class="text-gray-500 mb-4">Belum ada riwayat import.</p>
                    <a href="{{ route('imports.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-upload mr-2"></i>Upload File Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
