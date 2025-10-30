@extends('layouts.app')

@section('title', 'Detail Fixed Asset')

@section('content')
<div class="w-full">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Detail Fixed Asset</h1>
            <p class="text-sm text-gray-600">Informasi lengkap aset tetap</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('fixed-assets.edit', $fixedAsset) }}" 
               class="inline-flex items-center px-3 py-1.5 bg-yellow-600 border border-transparent rounded text-xs text-white hover:bg-yellow-700 transition">
                <i class="fas fa-edit mr-1"></i>
                Edit
            </a>
            <a href="{{ route('fixed-assets.index') }}" 
               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded text-xs text-gray-700 bg-white hover:bg-gray-50 transition">
                <i class="fas fa-arrow-left mr-1"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Asset Information Card -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden mb-3">
        <div class="px-3 py-2 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                Informasi Aset
            </h3>
        </div>
        <div class="p-3">
            <div class="space-y-2">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Kode Asset</dt>
                        <dd class="text-sm text-gray-900 font-semibold">{{ $fixedAsset->kode }}</dd>
                    </div>
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Kode Manual</dt>
                        <dd class="text-sm text-gray-900">{{ $fixedAsset->kode_manual ?: '-' }}</dd>
                    </div>
                </div>

                <div class="flex items-start py-1.5 border-b border-gray-100">
                    <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0 pt-1">Nama Asset</dt>
                    <dd class="text-sm text-gray-900 font-medium">{{ $fixedAsset->nama_fixed_asset }}</dd>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Tipe Asset</dt>
                        <dd class="text-sm text-gray-900">{{ optional($fixedAsset->typeRef)->name ?? '-' }}</dd>
                    </div>
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Lokasi</dt>
                        <dd class="text-sm text-gray-900">{{ optional($fixedAsset->location)->name ?? '-' }}</dd>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Status</dt>
                        <dd class="text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if(optional($fixedAsset->statusRef)->slug == 'aktif') bg-green-100 text-green-800
                                @elseif(optional($fixedAsset->statusRef)->slug == 'maintenance') bg-yellow-100 text-yellow-800
                                @elseif(optional($fixedAsset->statusRef)->slug == 'rusak') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ optional($fixedAsset->statusRef)->name ?? '-' }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Kondisi</dt>
                        <dd class="text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if(optional($fixedAsset->conditionRef)->slug == 'baik') bg-green-100 text-green-800
                                @elseif(optional($fixedAsset->conditionRef)->slug == 'rusak_ringan') bg-yellow-100 text-yellow-800
                                @elseif(optional($fixedAsset->conditionRef)->slug == 'rusak_berat') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ optional($fixedAsset->conditionRef)->name ?? '-' }}
                            </span>
                        </dd>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Taksiran Umur</dt>
                        <dd class="text-sm text-gray-900">{{ $fixedAsset->taksiran_umur }} tahun</dd>
                    </div>
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Efektif Mulai</dt>
                        <dd class="text-sm text-gray-900">{{ $fixedAsset->efektif_mulai->format('d F Y') }}</dd>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">PIC</dt>
                        <dd class="text-sm text-gray-900">{{ $fixedAsset->pic }}</dd>
                    </div>
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Cek Fisik</dt>
                        <dd class="text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $fixedAsset->harus_dicek_fisik ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $fixedAsset->harus_dicek_fisik ? 'Ya' : 'Tidak' }}
                            </span>
                        </dd>
                    </div>
                </div>

                @if($fixedAsset->deskripsi)
                <div class="flex items-start py-1.5">
                    <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0 pt-1">Deskripsi</dt>
                    <dd class="text-sm text-gray-900 leading-relaxed">{{ $fixedAsset->deskripsi }}</dd>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Technical Information -->
    @php($vendorName = optional($fixedAsset->vendorRef)->name)
    @php($brandName = optional($fixedAsset->brandRef)->name)
    @if($vendorName || $brandName || $fixedAsset->code_type || $fixedAsset->serial_number)
    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden mb-3">
        <div class="px-3 py-2 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-cogs mr-2 text-green-600"></i>
                Informasi Teknis
            </h3>
        </div>
        <div class="p-3">
            <div class="space-y-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @if($vendorName)
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Vendor</dt>
                        <dd class="text-sm text-gray-900">{{ $vendorName }}</dd>
                    </div>
                    @endif

                    @if($brandName)
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Brand</dt>
                        <dd class="text-sm text-gray-900">{{ $brandName }}</dd>
                    </div>
                    @endif
                </div>

                @if($fixedAsset->code_type || $fixedAsset->serial_number)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @if($fixedAsset->code_type)
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Code Type</dt>
                        <dd class="text-sm text-gray-900">{{ $fixedAsset->code_type }}</dd>
                    </div>
                    @endif

                    @if($fixedAsset->serial_number)
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Serial Number</dt>
                        <dd class="text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded">{{ $fixedAsset->serial_number }}</dd>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- System Information & QR Code -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-3">
        <!-- System Info -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-3 py-2 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-database mr-2 text-purple-600"></i>
                    Informasi Sistem
                </h3>
            </div>
            <div class="p-3">
                <div class="space-y-2">
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">ID Aset</dt>
                        <dd class="text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded">#{{ $fixedAsset->id }}</dd>
                    </div>
                    <div class="flex items-center py-1.5 border-b border-gray-100">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Dibuat</dt>
                        <dd class="text-sm text-gray-900">{{ $fixedAsset->created_at->format('d F Y, H:i') }} WIB</dd>
                    </div>
                    <div class="flex items-center py-1.5">
                        <dt class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">Diperbarui</dt>
                        <dd class="text-sm text-gray-900">{{ $fixedAsset->updated_at->format('d F Y, H:i') }} WIB</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-3 py-2 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-qrcode mr-2 text-indigo-600"></i>
                    QR Code Asset
                </h3>
            </div>
            <div class="p-3 flex justify-center">
                <div class="text-center">
                    <x-qr-code :fixed-asset="$fixedAsset" :size="130" />
                    <p class="text-xs text-gray-500 mt-1">Scan untuk akses cepat</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-3 py-2 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-tools mr-2 text-orange-600"></i>
                Aksi
            </h3>
        </div>
        <div class="p-3">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-3 sm:space-y-0 sm:space-x-3">
                <!-- Primary Actions -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('fixed-assets.edit', $fixedAsset) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Asset
                    </a>
                    <a href="{{ route('fixed-assets.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali ke Daftar
                    </a>
                </div>

                <!-- Danger Action -->
                <form action="{{ route('fixed-assets.destroy', $fixedAsset) }}" method="POST" 
                      x-data="{ deleting: false }"
                      @submit="deleting = true"
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus asset ini? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            :disabled="deleting"
                            :class="deleting ? 'opacity-50 cursor-not-allowed' : ''"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        <template x-if="!deleting">
                            <div class="flex items-center">
                                <i class="fas fa-trash mr-2"></i>
                                <span>Hapus Asset</span>
                            </div>
                        </template>
                        <template x-if="deleting">
                            <div class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Menghapus...</span>
                            </div>
                        </template>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
