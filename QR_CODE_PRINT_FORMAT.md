# Format Print QR Code - Asset Management System

## Overview
Format tampilan print QR code telah diperbarui untuk mengikuti standar label stiker dengan layout horizontal.

## Format Baru

### Layout
```
+------------------------------------------+
|  +--------+  +----------------------+   |
|  |        |  |       LOGO           |   |
|  |   QR   |  +----------------------+   |
|  |  CODE  |  |                      |   |
|  |        |  |    NAMA BARANG       |   |
|  |        |  |                      |   |
|  +--------+  +----------------------+   |
|              |   KODE MANUAL        |   |
|              +----------------------+   |
+------------------------------------------+
```

### Komponen
1. **QR Code (Kiri)**
   - Ukuran: 120x120 px
   - Border: 1px solid black
   - Padding: 5px
   - Posisi: Kiri, flex-shrink: 0

2. **Logo (Kanan Atas)**
   - Path: `/public/images/logo.png`
   - Max height: 35px
   - Alignment: Center
   - Fallback: Hidden jika tidak ditemukan

3. **Nama Barang (Kanan Tengah)**
   - Font size: 11px
   - Font weight: Bold
   - Alignment: Center
   - Word wrap: break-word
   - Vertical alignment: Center

4. **Kode Manual (Kanan Bawah)**
   - Font size: 10px
   - Font weight: Bold
   - Font family: 'Courier New', monospace
   - Border: 1px solid black
   - Padding: 3px 5px
   - Fallback: Menggunakan `kode` jika `kode_manual` kosong

## File yang Diubah

### 1. `resources/views/qr-codes/printable-asset.blade.php`
File template untuk halaman print QR code.

**Perubahan utama:**
- Layout flex horizontal (QR code kiri, info kanan)
- Tambah section logo
- Simplifikasi layout menjadi label stiker
- Optimasi untuk print dengan ukuran lebih kecil
- Responsive untuk mobile dan print

## Cara Menggunakan

### 1. Print QR Code dari Detail Asset
```
1. Buka halaman detail asset
2. Klik tombol "Print" pada QR code
3. Atau akses langsung: /qr-code/asset/{id}/print
```

### 2. Print dengan Auto-Print
```
URL: /qr-code/asset/{id}/print?autoprint=1
```
Browser akan otomatis membuka dialog print.

### 3. Download QR Code
```
URL: /qr-code/asset/{id}/download?size=400&format=svg
```

## Routes Terkait

```php
// Print QR Code (Printable View)
Route::get('/qr-code/asset/{fixedAsset}/print', [QRCodeController::class, 'printableAsset'])
    ->name('qr.asset.print');

// Generate QR Code (Image Only)
Route::get('/qr-code/asset/{fixedAsset}', [QRCodeController::class, 'fixedAsset'])
    ->name('qr.asset');

// Download QR Code
Route::get('/qr-code/asset/{fixedAsset}/download', [QRCodeController::class, 'download'])
    ->name('qr.asset.download');
```

## Print Settings

### Page Setup untuk Print (Default - Custom Label Size)
- **Size: 100mm x 60mm** (Custom label size)
- **Margin: 0mm** (no margin)
- **Orientation: Landscape**
- **Scale: 100%**
- Container padding: 5mm

Dengan setting ini, ukuran kertas akan otomatis menyesuaikan dengan ukuran label, tidak lagi full A4.

### Alternative: Multiple Labels per A4
Jika ingin cetak beberapa label dalam 1 halaman A4:
1. Buka file `printable-asset.blade.php`
2. Comment CSS `@media print` pertama (line 123-170)
3. Uncomment CSS alternative (line 174-192)

### Rekomendasi Printer
- **Label printer:** Ukuran 100mm x 60mm (atau 60mm x 40mm dengan adjustment)
- **Printer biasa:** Set custom paper size ke 100mm x 60mm
- **A4 sheet:** Gunakan label stiker A4 dengan grid 2 kolom

### Cara Set Custom Paper Size di Browser
#### Chrome/Edge:
1. Tekan Ctrl+P untuk print
2. Klik "More settings"
3. Paper size: Pilih "Custom" atau akan otomatis detect
4. Margins: None atau Minimum

#### Firefox:
1. Tekan Ctrl+P untuk print
2. Page setup → Paper size: Custom
3. Width: 100mm, Height: 60mm
4. Margins: 0

### Tips Print
- Jika printer tidak support custom size, gunakan "Fit to page" atau "Scale to fit"
- Untuk label stiker, gunakan actual size (100%)
- Preview terlebih dahulu sebelum print

## CSS Classes

### Main Classes
- `.label-stiker` - Container utama label
- `.label-content` - Flex container untuk layout
- `.qr-code` - Container QR code (kiri)
- `.asset-info` - Container info asset (kanan)
- `.logo-section` - Section untuk logo
- `.nama-barang` - Section untuk nama barang
- `.kode-manual` - Section untuk kode manual

### Helper Classes
- `.no-print` - Element yang tidak akan diprint
- `.instructions` - Panduan penggunaan (tidak diprint)

## Database Fields

Field yang digunakan dari model `FixedAsset`:
- `id` - ID asset
- `kode` - Kode otomatis (fallback)
- `kode_manual` - Kode manual barang (primary)
- `nama_fixed_asset` - Nama barang

## Testing

### Manual Test Steps
1. Buka halaman detail asset
2. Klik tombol "Print" pada QR code
3. Verifikasi layout:
   - QR code muncul di kiri
   - Logo muncul di kanan atas (jika ada)
   - Nama barang muncul di tengah kanan
   - Kode manual muncul di bawah kanan dengan border
4. Test print preview (Ctrl+P)
5. Verifikasi semua element tercetak dengan baik

### Browser Compatibility
- Chrome/Edge: ✓ Tested
- Firefox: ✓ Tested
- Safari: ✓ Should work

## Troubleshooting

### Logo tidak muncul
- Pastikan file `/public/images/logo.png` ada
- Check permission file
- Logo akan hidden otomatis jika tidak ditemukan (tidak error)

### QR Code tidak terbaca
- Pastikan ukuran minimum 100x100px
- Check error correction level: 'M' (Medium)
- Pastikan margin cukup (2px default)

### Print terpotong
- Adjust page margins di print settings
- Pastikan scale 100%
- Gunakan orientation yang sesuai

## Future Improvements

1. **Multi-label print** - Print beberapa label sekaligus
2. **Batch print** - Print semua asset dalam satu page
3. **Custom logo** - Upload logo per departemen/lokasi
4. **Template variants** - Berbagai ukuran label
5. **Barcode option** - Tambahan barcode 1D selain QR code

## Notes

- Format ini optimal untuk label printer ukuran 60x40mm atau lebih
- QR code menggunakan SVG untuk kualitas terbaik
- Auto-print delay 500ms untuk memastikan render lengkap
- Logo bersifat optional, layout tetap bagus tanpa logo
