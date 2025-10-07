@extends('layouts.app')

@section('title', 'Fixed Assets')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fixed Assets</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola aset tetap rumah sakit</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('fixed-assets.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i>
                Tambah Fixed Asset
            </a>
        </div>
    </div>

    <!-- Filters -->
    <x-table-filter 
        :filters="[
            'search' => request('search', ''),
            'status' => request('status', ''),
            'kondisi' => request('kondisi', ''),
            'dateFrom' => request('date_from', ''),
            'dateTo' => request('date_to', '')
        ]"
        searchPlaceholder="Cari berdasarkan nama, kode, lokasi, PIC..."
        :showDateRange="true"
    />

    <!-- Additional Filters -->
    <div class="bg-white p-4 border border-gray-200 shadow-sm rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        onchange="applyStatusFilter()">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\FixedAsset::getStatusOptions() as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Condition Filter -->
            <div>
                <label for="kondisi" class="block text-sm font-medium text-gray-700 mb-1">Kondisi</label>
                <select id="kondisi" name="kondisi" 
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        onchange="applyConditionFilter()">
                    <option value="">Semua Kondisi</option>
                    @foreach(\App\Models\FixedAsset::getConditionOptions() as $value => $label)
                        <option value="{{ $value }}" {{ request('kondisi') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end">
                <button type="button" onclick="clearAllFilters()" 
                        class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fas fa-times mr-2"></i>
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <x-table-responsive>
            <x-slot name="header">
                <tr class="bg-gray-50">
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Asset</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kondisi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse($fixedAssets as $asset)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900">
                        <div class="font-medium">{{ $asset->kode }}</div>
                        @if($asset->kode_manual)
                            <div class="text-xs text-gray-500">{{ $asset->kode_manual }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                        <div class="font-medium">{{ $asset->nama_fixed_asset }}</div>
                        @if($asset->brand)
                            <div class="text-xs text-gray-500">{{ $asset->brand }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $asset->tipe_fixed_asset }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $asset->lokasi }}</td>
                    <td class="px-4 py-3 text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($asset->status == 'aktif') bg-green-100 text-green-800
                            @elseif($asset->status == 'maintenance') bg-yellow-100 text-yellow-800
                            @elseif($asset->status == 'rusak') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $asset->status_display }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($asset->kondisi == 'baik') bg-green-100 text-green-800
                            @elseif($asset->kondisi == 'rusak_ringan') bg-yellow-100 text-yellow-800
                            @elseif($asset->kondisi == 'rusak_berat') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $asset->condition_display }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $asset->pic }}</td>
                    <td class="px-4 py-3 text-sm font-medium">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('fixed-assets.show', $asset) }}" 
                               class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('fixed-assets.edit', $asset) }}" 
                               class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('fixed-assets.destroy', $asset) }}" method="POST" 
                                  class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus fixed asset ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                        <div>Tidak ada fixed asset ditemukan</div>
                    </td>
                </tr>
                @endforelse
            </x-slot>
        </x-table-responsive>
    </div>

    <!-- Pagination -->
    @if($fixedAssets->hasPages())
    <div class="mt-6">
        {{ $fixedAssets->links() }}
    </div>
    @endif
</div>

<script>
function applyStatusFilter() {
    const status = document.getElementById('status').value;
    const url = new URL(window.location);
    
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    
    window.location.href = url.toString();
}

function applyConditionFilter() {
    const kondisi = document.getElementById('kondisi').value;
    const url = new URL(window.location);
    
    if (kondisi) {
        url.searchParams.set('kondisi', kondisi);
    } else {
        url.searchParams.delete('kondisi');
    }
    
    window.location.href = url.toString();
}

function clearAllFilters() {
    const url = new URL(window.location);
    url.search = '';
    window.location.href = url.toString();
}
</script>
@endsection
