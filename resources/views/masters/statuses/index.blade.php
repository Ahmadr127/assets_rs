@extends('layouts.app')

@section('title', 'Master Status')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Master Status</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola data status aset</p>
        </div>
        <a href="{{ route('masters.statuses.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
            <i class="fas fa-plus mr-2"></i> Tambah Status
        </a>
    </div>

    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <x-table-responsive>
            <x-slot name="header">
                <tr class="bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->slug }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</td>
                    <td class="px-4 py-3 text-sm font-medium">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('masters.statuses.edit', $item) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('masters.statuses.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus status ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada data</td>
                </tr>
                @endforelse
            </x-slot>
        </x-table-responsive>
    </div>

    @if($items->hasPages())
    <div class="mt-6">{{ $items->links() }}</div>
    @endif
</div>
@endsection
