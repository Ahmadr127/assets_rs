<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $fixedAsset->nama_fixed_asset }} - Asset Detail</title>
    <!-- @vite('resources/css/app.css') -->
    <script src="https://cdn.tailwindcss.com"></script>
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
                        <p class="text-xs text-gray-500 mt-0.5">Code: <span class="font-mono font-semibold">{{ $fixedAsset->kode_manual }}</span></p>
                    </div>
                    <div class="text-right">
                        <a href="{{ route('login') }}" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 transition">
                            <i class="fas fa-sign-in-alt mr-1.5 text-xs"></i>
                            Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- Asset Information - Only showing specified fields -->
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
                            @if($fixedAsset->kode_manual)
                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Kode Manual</label>
                                <p class="text-sm text-gray-900 flex-1">{{ $fixedAsset->kode_manual }}</p>
                            </div>
                            @endif

                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Nama Asset</label>
                                <p class="text-sm text-gray-900 flex-1">{{ $fixedAsset->nama_fixed_asset }}</p>
                            </div>

                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Efektif Mulai</label>
                                <p class="text-sm text-gray-900 flex-1">
                                    @if($fixedAsset->efektif_mulai)
                                        {{ $fixedAsset->efektif_mulai->format('d F Y') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="space-y-1.5">
                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Lokasi</label>
                                <p class="text-sm text-gray-900 flex-1">{{ optional($fixedAsset->location)->name ?? '-' }}</p>
                            </div>

                            @if($fixedAsset->po)
                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Purchase Order</label>
                                <p class="text-sm text-gray-900 flex-1">{{ $fixedAsset->po }}</p>
                            </div>
                            @endif

                            <div class="flex items-start py-1.5 border-b border-gray-100">
                                <label class="text-xs font-semibold text-gray-500 uppercase w-28 flex-shrink-0">Asset Number</label>
                                <p class="text-sm text-gray-900 flex-1">{{ $fixedAsset->asset_number ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-xs text-gray-500 mt-4 pb-2">
                <p>Asset Management System</p>
                <p class="mt-0.5">Scan QR code untuk melihat detail asset</p>
            </div>
        </div>
    </div>
</body>
</html>