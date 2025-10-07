@extends('layouts.app')

@section('title', 'Detail Fixed Asset')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Fixed Asset</h1>
                <p class="mt-1 text-sm text-gray-600">Informasi lengkap aset tetap</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('fixed-assets.edit', $fixedAsset) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('fixed-assets.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Asset Information -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Aset</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Kode</label>
                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ $fixedAsset->kode }}</p>
                            @if($fixedAsset->kode_manual)
                                <p class="text-xs text-gray-500">Manual: {{ $fixedAsset->kode_manual }}</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nama Fixed Asset</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->nama_fixed_asset }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Tipe Fixed Asset</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->tipe_fixed_asset }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Lokasi</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->lokasi }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">PIC (Person In Charge)</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->pic }}</p>
                        </div>
                    </div>

                    <!-- Status and Condition -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($fixedAsset->status == 'aktif') bg-green-100 text-green-800
                                    @elseif($fixedAsset->status == 'maintenance') bg-yellow-100 text-yellow-800
                                    @elseif($fixedAsset->status == 'rusak') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $fixedAsset->status_display }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Kondisi</label>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($fixedAsset->kondisi == 'baik') bg-green-100 text-green-800
                                    @elseif($fixedAsset->kondisi == 'rusak_ringan') bg-yellow-100 text-yellow-800
                                    @elseif($fixedAsset->kondisi == 'rusak_berat') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $fixedAsset->condition_display }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Taksiran Umur</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->taksiran_umur }} tahun</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Efektif Mulai</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->efektif_mulai->format('d M Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Harus Dicek Fisik</label>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $fixedAsset->harus_dicek_fisik ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $fixedAsset->harus_dicek_fisik ? 'Ya' : 'Tidak' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                @if($fixedAsset->deskripsi)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-500">Deskripsi</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->deskripsi }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Technical Information -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Teknis</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        @if($fixedAsset->vendor)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Vendor</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->vendor }}</p>
                        </div>
                        @endif

                        @if($fixedAsset->brand)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Brand</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->brand }}</p>
                        </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        @if($fixedAsset->code_type)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Code Type</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->code_type }}</p>
                        </div>
                        @endif

                        @if($fixedAsset->serial_number)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Serial Number</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono">{{ $fixedAsset->serial_number }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Sistem</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Dibuat Pada</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->created_at->format('d M Y, H:i') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Terakhir Diperbarui</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $fixedAsset->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">ID Aset</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono">#{{ $fixedAsset->id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <form action="{{ route('fixed-assets.destroy', $fixedAsset) }}" method="POST" 
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus fixed asset ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-trash mr-2"></i>
                    Hapus
                </button>
            </form>

            <div class="flex items-center space-x-3">
                <a href="{{ route('fixed-assets.edit', $fixedAsset) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('fixed-assets.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fas fa-list mr-2"></i>
                    Daftar Aset
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
