<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $fixedAsset->nama_fixed_asset }} - Asset Detail</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-3 px-3">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-3 mb-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">{{ $fixedAsset->nama_fixed_asset }}</h1>
                        <p class="text-xs text-gray-500 mt-0.5">Code: <span class="font-mono font-semibold">{{ $fixedAsset->kode }}</span></p>
                    </div>
                    <div class="text-right">
                        <a href="{{ route('login') }}" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition">
                            <i class="fas fa-sign-in-alt mr-1.5 text-xs"></i>
                            Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- Asset Information -->
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden mb-3">
                <div class="px-3 py-2 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle mr-1.5 text-blue-600 text-xs"></i>
                        Informasi Aset
                    </h3>
                </div>
                <div class="p-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                        <!-- Column 1 -->
                        <div class="space-y-1.5">
                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Kode Asset</label>
                                <p class="text-sm font-semibold text-gray-900 flex-1">{{ $fixedAsset->kode }}</p>
                            </div>
                            
                            @if($fixedAsset->kode_manual)
                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Kode Manual</label>
                                <p class="text-sm text-gray-900 flex-1">{{ $fixedAsset->kode_manual }}</p>
                            </div>
                            @endif

                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Tipe Asset</label>
                                <p class="text-sm text-gray-900 flex-1">{{ optional($fixedAsset->typeRef)->name ?? '-' }}</p>
                            </div>

                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Lokasi</label>
                                <p class="text-sm text-gray-900 flex-1">{{ optional($fixedAsset->location)->name ?? '-' }}</p>
                            </div>

                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">PIC</label>
                                <p class="text-sm text-gray-900 flex-1">{{ $fixedAsset->pic }}</p>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="space-y-1.5">
                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Status</label>
                                <div class="flex-1">
                                    @php($statusSlug = optional($fixedAsset->statusRef)->slug)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($statusSlug === 'aktif') bg-green-100 text-green-800
                                        @elseif($statusSlug === 'maintenance') bg-yellow-100 text-yellow-800
                                        @elseif($statusSlug === 'rusak') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ optional($fixedAsset->statusRef)->name ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Kondisi</label>
                                <div class="flex-1">
                                    @php($condSlug = optional($fixedAsset->conditionRef)->slug)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($condSlug === 'baik') bg-green-100 text-green-800
                                        @elseif($condSlug === 'rusak_ringan') bg-yellow-100 text-yellow-800
                                        @elseif($condSlug === 'rusak_berat') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ optional($fixedAsset->conditionRef)->name ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Taksiran Umur</label>
                                <p class="text-sm text-gray-900 flex-1">{{ $fixedAsset->taksiran_umur }} tahun</p>
                            </div>

                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Efektif Mulai</label>
                                <p class="text-sm text-gray-900 flex-1">{{ $fixedAsset->efektif_mulai->format('d F Y') }}</p>
                            </div>

                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Cek Fisik</label>
                                <div class="flex-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $fixedAsset->harus_dicek_fisik ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $fixedAsset->harus_dicek_fisik ? 'Ya' : 'Tidak' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($fixedAsset->deskripsi)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex items-start">
                            <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Deskripsi</label>
                            <p class="text-sm text-gray-900 flex-1 leading-relaxed">{{ $fixedAsset->deskripsi }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Technical Information -->
            @php($vendorName = optional($fixedAsset->vendorRef)->name)
            @php($brandName = optional($fixedAsset->brandRef)->name)
            @if($vendorName || $brandName || $fixedAsset->code_type || $fixedAsset->serial_number)
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden mb-3">
                <div class="px-3 py-2 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-cogs mr-1.5 text-green-600 text-xs"></i>
                        Informasi Teknis
                    </h3>
                </div>
                <div class="p-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1.5">
                        @if($vendorName)
                        <div class="flex items-start py-1.5 border-b border-gray-100">
                            <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Vendor</label>
                            <p class="text-sm text-gray-900 flex-1">{{ $vendorName }}</p>
                        </div>
                        @endif

                        @if($brandName)
                        <div class="flex items-start py-1.5 border-b border-gray-100">
                            <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Brand</label>
                            <p class="text-sm text-gray-900 flex-1">{{ $brandName }}</p>
                        </div>
                        @endif

                        @if($fixedAsset->code_type)
                        <div class="flex items-start py-1.5 border-b border-gray-100">
                            <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Code Type</label>
                            <p class="text-sm text-gray-900 flex-1">{{ $fixedAsset->code_type }}</p>
                        </div>
                        @endif

                        @if($fixedAsset->serial_number)
                        <div class="flex items-start py-1.5 border-b border-gray-100">
                            <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Serial Number</label>
                            <p class="text-sm text-gray-900 flex-1 font-mono bg-gray-50 px-2 py-1 rounded inline-block">{{ $fixedAsset->serial_number }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Footer -->
            <div class="text-center text-xs text-gray-500 mt-4 pb-2">
                <p>Asset Management System</p>
                <p class="mt-0.5">Scan QR code untuk melihat detail asset</p>
            </div>
        </div>
    </div>
</body>
</html>
