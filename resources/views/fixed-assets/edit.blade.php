@extends('layouts.app')

@section('title', 'Edit Fixed Asset')

@section('content')
<div class="w-full">
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Fixed Asset</h1>
            <p class="text-sm text-gray-600">Perbarui informasi aset tetap</p>
        </div>
        <a href="{{ route('fixed-assets.index') }}" 
           class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded text-xs text-gray-700 bg-white hover:bg-gray-50 transition">
            <i class="fas fa-arrow-left mr-1"></i>
            Kembali
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white shadow border border-gray-200 rounded">
        <form action="{{ route('fixed-assets.update', $fixedAsset) }}" method="POST" class="p-4">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <!-- Tipe Fixed Asset -->
                <div>
                    <x-form.async-select name="asset_type_id" entity="types"
                                         label="Tipe Fixed Asset"
                                         :value="$fixedAsset->asset_type_id"
                                         :text="optional($fixedAsset->typeRef)->name"
                                         :required="true"
                                         placeholder="Contoh: Komputer, Printer, AC" />
                </div>

                <!-- Kode -->
                <div>
                    <label for="kode" class="block text-xs font-medium text-gray-700 mb-1">
                        Kode <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="kode" 
                           name="kode" 
                           value="{{ old('kode', $fixedAsset->kode) }}"
                           required
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 @error('kode') border-red-300 @enderror"
                           placeholder="PC-001">
                    @error('kode')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                <!-- Kode Manual -->
                <div>
                    <label for="kode_manual" class="block text-xs font-medium text-gray-700 mb-1">
                        Kode Manual
                    </label>
                    <input type="text" 
                           id="kode_manual" 
                           name="kode_manual" 
                           value="{{ old('kode_manual', $fixedAsset->kode_manual) }}"
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 @error('kode_manual') border-red-300 @enderror"
                           placeholder="Manual">
                    @error('kode_manual')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Nama Fixed Asset -->
                <div class="md:col-span-2">
                    <label for="nama_fixed_asset" class="block text-xs font-medium text-gray-700 mb-1">
                        Nama Fixed Asset <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nama_fixed_asset" 
                           name="nama_fixed_asset" 
                           value="{{ old('nama_fixed_asset', $fixedAsset->nama_fixed_asset) }}"
                           required
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 @error('nama_fixed_asset') border-red-300 @enderror"
                           placeholder="Nama lengkap aset">
                    @error('nama_fixed_asset')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Taksiran Umur -->
                <div>
                    <label for="taksiran_umur" class="block text-xs font-medium text-gray-700 mb-1">
                        Umur (Thn) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="taksiran_umur" 
                           name="taksiran_umur" 
                           value="{{ old('taksiran_umur', $fixedAsset->taksiran_umur) }}"
                           min="1" max="100"
                           required
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 @error('taksiran_umur') border-red-300 @enderror"
                           placeholder="5">
                    @error('taksiran_umur')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Efektif Mulai -->
                <div>
                    <label for="efektif_mulai" class="block text-xs font-medium text-gray-700 mb-1">
                        Efektif Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="efektif_mulai" 
                           name="efektif_mulai" 
                           value="{{ old('efektif_mulai', $fixedAsset->efektif_mulai->format('Y-m-d')) }}"
                           required
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 @error('efektif_mulai') border-red-300 @enderror">
                    @error('efektif_mulai')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Lokasi -->
                <div>
                    <x-form.async-select name="location_id" entity="locations"
                                         label="Lokasi"
                                         :value="$fixedAsset->location_id"
                                         :text="optional($fixedAsset->location)->name"
                                         :required="true"
                                         placeholder="Contoh: Unit IT, Sekretaris, Ruang Rapat" />
                </div>

                <!-- Status -->
                <div>
                    <x-form.combobox name="status_id" label="Status" :options="$statuses" :value="$fixedAsset->status_id" :required="true" placeholder="Pilih Status..." :searchable="true" :clearable="false" />
                </div>

                <!-- Kondisi -->
                <div>
                    <x-form.async-select name="condition_id" entity="conditions"
                                         label="Kondisi"
                                         :value="$fixedAsset->condition_id"
                                         :text="optional($fixedAsset->conditionRef)->name"
                                         :required="true"
                                         placeholder="Contoh: Baik, Rusak Ringan, Rusak Berat" />
                </div>

                <!-- Vendor -->
                <div>
                    <x-form.async-select name="vendor_id" entity="vendors"
                                         label="Vendor"
                                         :value="$fixedAsset->vendor_id"
                                         :text="optional($fixedAsset->vendorRef)->name"
                                         placeholder="Contoh: PT ABC, CV XYZ, Toko Komputer" />
                </div>

                <!-- Brand -->
                <div>
                    <x-form.async-select name="brand_id" entity="brands"
                                         label="Brand"
                                         :value="$fixedAsset->brand_id"
                                         :text="optional($fixedAsset->brandRef)->name"
                                         placeholder="Contoh: HP, Dell, Canon, Samsung" />
                </div>

                <!-- Code Type -->
                <div>
                    <label for="code_type" class="block text-xs font-medium text-gray-700 mb-1">
                        Code Type
                    </label>
                    <input type="text" 
                           id="code_type" 
                           name="code_type" 
                           value="{{ old('code_type', $fixedAsset->code_type) }}"
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 @error('code_type') border-red-300 @enderror"
                           placeholder="Tipe kode">
                    @error('code_type')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- Serial Number -->
                <div>
                    <label for="serial_number" class="block text-xs font-medium text-gray-700 mb-1">
                        Serial Number
                    </label>
                    <input type="text" 
                           id="serial_number" 
                           name="serial_number" 
                           value="{{ old('serial_number', $fixedAsset->serial_number) }}"
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 @error('serial_number') border-red-300 @enderror"
                           placeholder="Nomor seri">
                    @error('serial_number')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <!-- PIC -->
                <div>
                    <label for="pic" class="block text-xs font-medium text-gray-700 mb-1">
                        PIC <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="pic" 
                           name="pic" 
                           value="{{ old('pic', $fixedAsset->pic) }}"
                           required
                           class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 @error('pic') border-red-300 @enderror"
                           placeholder="Penanggung jawab">
                    @error('pic')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="mt-3">
                <label for="deskripsi" class="block text-xs font-medium text-gray-700 mb-1">
                    Deskripsi
                </label>
                <textarea id="deskripsi" 
                          name="deskripsi" 
                          rows="2"
                          class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 @error('deskripsi') border-red-300 @enderror"
                          placeholder="Deskripsi detail aset">{{ old('deskripsi', $fixedAsset->deskripsi) }}</textarea>
                @error('deskripsi')<p class="mt-0.5 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Harus Dicek Fisik -->
            <div class="mt-3">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="harus_dicek_fisik" 
                           name="harus_dicek_fisik" 
                           value="1"
                           {{ old('harus_dicek_fisik', $fixedAsset->harus_dicek_fisik) ? 'checked' : '' }}
                           class="h-3 w-3 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="harus_dicek_fisik" class="ml-2 block text-xs text-gray-900">
                        Harus Dicek Fisik
                    </label>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-2 mt-4 pt-3 border-t border-gray-200">
                <a href="{{ route('fixed-assets.index') }}" 
                   class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded text-xs text-gray-700 bg-white hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Perbarui
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
