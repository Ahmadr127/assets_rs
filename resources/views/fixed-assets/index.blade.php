@extends('layouts.app')

@section('title', 'Fixed Assets')

@section('content')
<div class="w-full">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Fixed Assets</h1>
            <p class="text-sm text-gray-600">Kelola aset tetap rumah sakit</p>
        </div>
        <a href="{{ route('fixed-assets.create') }}" 
           class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded text-xs text-white hover:bg-green-700 transition">
            <i class="fas fa-plus mr-1"></i>
            Tambah
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white p-2 border border-gray-200 shadow rounded mb-3">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
            <!-- Search -->
            <div class="md:col-span-2">
                <input type="text" id="search" name="search" value="{{ request('search') }}"
                       class="block w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Cari nama, kode, lokasi, PIC..." onkeyup="debounceSearch(this.value)">
            </div>
            <!-- Status Filter -->
            <div>
                <select id="status" name="status_id" 
                        class="block w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                        onchange="applyStatusFilter()">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $id => $name)
                        <option value="{{ $id }}" {{ request('status_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Condition Filter -->
            <div>
                <select id="kondisi" name="condition_id" 
                        class="block w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                        onchange="applyConditionFilter()">
                    <option value="">Semua Kondisi</option>
                    @foreach($conditions as $id => $name)
                        <option value="{{ $id }}" {{ request('condition_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- Actions -->
            <div>
                <button type="button" onclick="clearAllFilters()" 
                        class="w-full inline-flex items-center justify-center px-2 py-1.5 border border-gray-300 rounded text-xs text-gray-700 bg-white hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-1"></i>
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow border border-gray-200 rounded overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Asset</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kondisi</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">PIC</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Efektif Mulai</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Taksiran Umur (thn)</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($fixedAssets as $index => $asset)
                    <tr class="hover:bg-gray-50">
                        <td class="px-2 py-2 text-xs text-gray-900 font-medium text-center">
                            {{ ($fixedAssets->currentPage() - 1) * $fixedAssets->perPage() + $index + 1 }}
                        </td>
                        <td class="px-2 py-2 text-xs text-gray-900">
                            <div class="font-medium">{{ $asset->kode }}</div>
                            @if($asset->kode_manual)
                                <div class="text-xs text-gray-500">{{ $asset->kode_manual }}</div>
                            @endif
                        </td>
                        <td class="px-2 py-2 text-xs text-gray-900">
                            <div class="font-medium">{{ Str::limit($asset->nama_fixed_asset, 30) }}</div>
                            @if(optional($asset->brandRef)->name)
                                <div class="text-xs text-gray-500">{{ $asset->brandRef->name }}</div>
                            @endif
                        </td>
                        <td class="px-2 py-2 text-xs text-gray-900">{{ optional($asset->typeRef)->name ?? '-' }}</td>
                        <td class="px-2 py-2 text-xs text-gray-900">{{ optional($asset->location)->name ?? '-' }}</td>
                        <td class="px-2 py-2 text-xs">
                            @php($statusSlug = optional($asset->statusRef)->slug)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                @if($statusSlug === 'aktif') bg-green-100 text-green-800
                                @elseif($statusSlug === 'maintenance') bg-yellow-100 text-yellow-800
                                @elseif($statusSlug === 'rusak') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ optional($asset->statusRef)->name ?? $asset->status_display }}
                            </span>
                        </td>
                        <td class="px-2 py-2 text-xs">
                            @php($condSlug = optional($asset->conditionRef)->slug)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                @if($condSlug === 'baik') bg-green-100 text-green-800
                                @elseif($condSlug === 'rusak_ringan') bg-yellow-100 text-yellow-800
                                @elseif($condSlug === 'rusak_berat') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ optional($asset->conditionRef)->name ?? $asset->condition_display }}
                            </span>
                        </td>
                        <td class="px-2 py-2 text-xs text-gray-900">{{ Str::limit($asset->pic, 20) }}</td>
                        <td class="px-2 py-2 text-xs text-gray-900">
                            @if($asset->efektif_mulai)
                                {{ $asset->efektif_mulai->format('d/m/Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-2 py-2 text-xs text-gray-900">
                            {{ $asset->taksiran_umur ? $asset->taksiran_umur . ' thn' : '-' }}
                        </td>
                        <td class="px-2 py-2 text-xs font-medium">
                            <div class="flex items-center space-x-1">
                                <a href="{{ route('fixed-assets.show', $asset) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 p-1" title="Lihat">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('fixed-assets.edit', $asset) }}" 
                                   class="text-yellow-600 hover:text-yellow-900 p-1" title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <form action="{{ route('fixed-assets.destroy', $asset) }}" method="POST" 
                                      class="inline" onsubmit="return confirm('Hapus asset ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-2 py-6 text-center text-xs text-gray-500">
                            <i class="fas fa-inbox text-2xl text-gray-300 mb-1"></i>
                            <div>Tidak ada data</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($fixedAssets->hasPages())
    <div class="mt-3">
        {{ $fixedAssets->links() }}
    </div>
    @endif
</div>

<script>
let searchTimeout;
function debounceSearch(value) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const url = new URL(window.location);
        if (value) {
            url.searchParams.set('search', value);
        } else {
            url.searchParams.delete('search');
        }
        window.location.href = url.toString();
    }, 500);
}

function applyStatusFilter() {
    const status = document.getElementById('status').value;
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status_id', status);
    } else {
        url.searchParams.delete('status_id');
    }
    window.location.href = url.toString();
}

function applyConditionFilter() {
    const kondisi = document.getElementById('kondisi').value;
    const url = new URL(window.location);
    if (kondisi) {
        url.searchParams.set('condition_id', kondisi);
    } else {
        url.searchParams.delete('condition_id');
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
