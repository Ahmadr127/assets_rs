@extends('layouts.app')

@section('title', 'Edit Fixed Asset')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Fixed Asset</h1>
                <p class="mt-1 text-sm text-gray-600">Perbarui informasi aset tetap</p>
            </div>
            <a href="{{ route('fixed-assets.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>

        <!-- Form -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg">
            <form action="{{ route('fixed-assets.update', $fixedAsset) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tipe Fixed Asset -->
                    <div>
                        <label for="tipe_fixed_asset" class="block text-sm font-medium text-gray-700 mb-1">
                            Tipe Fixed Asset <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="tipe_fixed_asset" 
                               name="tipe_fixed_asset" 
                               value="{{ old('tipe_fixed_asset', $fixedAsset->tipe_fixed_asset) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('tipe_fixed_asset') border-red-300 @enderror"
                               placeholder="Contoh: Komputer, Printer, Meja">
                        @error('tipe_fixed_asset')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kode -->
                    <div>
                        <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">
                            Kode <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="kode" 
                               name="kode" 
                               value="{{ old('kode', $fixedAsset->kode) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('kode') border-red-300 @enderror"
                               placeholder="Contoh: PC-001">
                        @error('kode')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kode Manual -->
                    <div>
                        <label for="kode_manual" class="block text-sm font-medium text-gray-700 mb-1">
                            Kode Manual
                        </label>
                        <input type="text" 
                               id="kode_manual" 
                               name="kode_manual" 
                               value="{{ old('kode_manual', $fixedAsset->kode_manual) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('kode_manual') border-red-300 @enderror"
                               placeholder="Kode manual opsional">
                        @error('kode_manual')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Fixed Asset -->
                    <div>
                        <label for="nama_fixed_asset" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Fixed Asset <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nama_fixed_asset" 
                               name="nama_fixed_asset" 
                               value="{{ old('nama_fixed_asset', $fixedAsset->nama_fixed_asset) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('nama_fixed_asset') border-red-300 @enderror"
                               placeholder="Nama lengkap aset">
                        @error('nama_fixed_asset')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Taksiran Umur -->
                    <div>
                        <label for="taksiran_umur" class="block text-sm font-medium text-gray-700 mb-1">
                            Taksiran Umur (Tahun) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="taksiran_umur" 
                               name="taksiran_umur" 
                               value="{{ old('taksiran_umur', $fixedAsset->taksiran_umur) }}"
                               min="1" max="100"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('taksiran_umur') border-red-300 @enderror"
                               placeholder="5">
                        @error('taksiran_umur')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Efektif Mulai -->
                    <div>
                        <label for="efektif_mulai" class="block text-sm font-medium text-gray-700 mb-1">
                            Efektif Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="efektif_mulai" 
                               name="efektif_mulai" 
                               value="{{ old('efektif_mulai', $fixedAsset->efektif_mulai->format('Y-m-d')) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('efektif_mulai') border-red-300 @enderror">
                        @error('efektif_mulai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lokasi -->
                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">
                            Lokasi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="lokasi" 
                               name="lokasi" 
                               value="{{ old('lokasi', $fixedAsset->lokasi) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('lokasi') border-red-300 @enderror"
                               placeholder="Contoh: Ruang Admin, Lab Komputer">
                        @error('lokasi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" 
                                name="status" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('status') border-red-300 @enderror">
                            <option value="">Pilih Status</option>
                            @foreach(\App\Models\FixedAsset::getStatusOptions() as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $fixedAsset->status) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kondisi -->
                    <div>
                        <label for="kondisi" class="block text-sm font-medium text-gray-700 mb-1">
                            Kondisi <span class="text-red-500">*</span>
                        </label>
                        <select id="kondisi" 
                                name="kondisi" 
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('kondisi') border-red-300 @enderror">
                            <option value="">Pilih Kondisi</option>
                            @foreach(\App\Models\FixedAsset::getConditionOptions() as $value => $label)
                                <option value="{{ $value }}" {{ old('kondisi', $fixedAsset->kondisi) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('kondisi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Vendor -->
                    <div>
                        <label for="vendor" class="block text-sm font-medium text-gray-700 mb-1">
                            Vendor
                        </label>
                        <input type="text" 
                               id="vendor" 
                               name="vendor" 
                               value="{{ old('vendor', $fixedAsset->vendor) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('vendor') border-red-300 @enderror"
                               placeholder="Nama vendor/pemasok">
                        @error('vendor')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Brand -->
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">
                            Brand
                        </label>
                        <input type="text" 
                               id="brand" 
                               name="brand" 
                               value="{{ old('brand', $fixedAsset->brand) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('brand') border-red-300 @enderror"
                               placeholder="Merek produk">
                        @error('brand')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Code Type -->
                    <div>
                        <label for="code_type" class="block text-sm font-medium text-gray-700 mb-1">
                            Code Type
                        </label>
                        <input type="text" 
                               id="code_type" 
                               name="code_type" 
                               value="{{ old('code_type', $fixedAsset->code_type) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('code_type') border-red-300 @enderror"
                               placeholder="Tipe kode">
                        @error('code_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Serial Number -->
                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-1">
                            Serial Number
                        </label>
                        <input type="text" 
                               id="serial_number" 
                               name="serial_number" 
                               value="{{ old('serial_number', $fixedAsset->serial_number) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('serial_number') border-red-300 @enderror"
                               placeholder="Nomor seri">
                        @error('serial_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PIC -->
                    <div>
                        <label for="pic" class="block text-sm font-medium text-gray-700 mb-1">
                            PIC (Person In Charge) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="pic" 
                               name="pic" 
                               value="{{ old('pic', $fixedAsset->pic) }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('pic') border-red-300 @enderror"
                               placeholder="Nama penanggung jawab">
                        @error('pic')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">
                        Deskripsi
                    </label>
                    <textarea id="deskripsi" 
                              name="deskripsi" 
                              rows="3"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('deskripsi') border-red-300 @enderror"
                              placeholder="Deskripsi detail aset">{{ old('deskripsi', $fixedAsset->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Harus Dicek Fisik -->
                <div>
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="harus_dicek_fisik" 
                               name="harus_dicek_fisik" 
                               value="1"
                               {{ old('harus_dicek_fisik', $fixedAsset->harus_dicek_fisik) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="harus_dicek_fisik" class="ml-2 block text-sm text-gray-900">
                            Harus Dicek Fisik
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Centang jika aset ini memerlukan pengecekan fisik berkala</p>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('fixed-assets.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-save mr-2"></i>
                        Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
