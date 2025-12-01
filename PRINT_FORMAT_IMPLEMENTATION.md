# Implementasi Fitur Print QR Code dengan Pilihan Ukuran

## 📋 Overview

Fitur ini memungkinkan user untuk memilih ukuran label QR code sebelum print dengan 4 pilihan ukuran:
- **6 x 5 cm** - Stiker Besar (default)
- **5 x 3 cm** - Stiker Sedang
- **5 x 2.5 cm** - Stiker Kecil
- **5 x 2 cm** - Stiker Mini

## 🗄️ Database Schema

### Table: `print_formats`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Nama format (e.g., "Stiker Besar") |
| code | varchar | Kode unik (e.g., "6x5") |
| width_cm | decimal(5,2) | Lebar dalam cm |
| height_cm | decimal(5,2) | Tinggi dalam cm |
| qr_size_px | integer | Ukuran QR code dalam pixel |
| margin_mm | integer | Margin dalam mm |
| font_size_name | integer | Font size untuk nama barang |
| font_size_code | integer | Font size untuk kode |
| is_active | boolean | Status aktif |
| is_default | boolean | Default format |
| sort_order | integer | Urutan tampilan |
| description | text | Deskripsi |
| created_at | timestamp | - |
| updated_at | timestamp | - |

## 📁 File yang Dibuat/Dimodifikasi

### 1. **Migration**
- `database/migrations/2025_12_01_000001_create_print_formats_table.php`

### 2. **Model**
- `app/Models/PrintFormat.php`
  - Method: `getWidthMm()`, `getHeightMm()`, `getDefault()`, `getActive()`

### 3. **Seeder**
- `database/seeders/PrintFormatSeeder.php`
- `database/seeders/DatabaseSeeder.php` (updated)

### 4. **Controller**
- `app/Http/Controllers/QRCodeController.php`
  - Method `printableAsset()` - Updated untuk support parameter `format`

### 5. **Views**
- `resources/views/qr-codes/printable-asset.blade.php` - Dynamic CSS
- `resources/views/components/qr-code.blade.php` - Dropdown selector

## 🚀 Cara Install

### 1. Jalankan Migration
```bash
php artisan migrate
```

### 2. Jalankan Seeder
```bash
php artisan db:seed --class=PrintFormatSeeder
```

Atau jika ingin seed semua:
```bash
php artisan db:seed
```

## 📖 Cara Penggunaan

### User Interface

1. **Pada halaman detail asset**, user akan melihat QR code component
2. **Dropdown "Ukuran Label"** menampilkan 4 pilihan ukuran
3. **Pilih ukuran** yang diinginkan
4. **Klik tombol "Print"**
5. **Browser akan membuka tab baru** dengan preview label
6. **Auto-print dialog** akan muncul
7. **Print** sesuai ukuran yang dipilih

### API Endpoint

```
GET /qr/asset/{fixedAsset}/print?format={code}&autoprint=1
```

**Parameters:**
- `format` (optional) - Kode format: `6x5`, `5x3`, `5x2.5`, `5x2`
- `autoprint` (optional) - Set `1` untuk auto-print

**Example:**
```
/qr/asset/123/print?format=5x3&autoprint=1
```

## 🔧 Konfigurasi Format

### Menambah Format Baru

Tambahkan data ke table `print_formats`:

```sql
INSERT INTO print_formats (
    name, code, width_cm, height_cm, 
    qr_size_px, margin_mm, font_size_name, font_size_code,
    is_active, is_default, sort_order, description
) VALUES (
    'Stiker Custom', '7x6', 7.0, 6.0,
    140, 6, 12, 11,
    true, false, 5, 'Ukuran custom untuk kebutuhan khusus'
);
```

### Mengubah Default Format

```sql
-- Set semua ke non-default
UPDATE print_formats SET is_default = false;

-- Set format tertentu sebagai default
UPDATE print_formats SET is_default = true WHERE code = '5x3';
```

### Menonaktifkan Format

```sql
UPDATE print_formats SET is_active = false WHERE code = '5x2';
```

## 🎨 Customization

### CSS Dinamis

View `printable-asset.blade.php` menggunakan CSS dinamis dari database:

```blade
@media print {
    @page {
        size: {{ $printFormat->getWidthMm() }}mm {{ $printFormat->getHeightMm() }}mm;
        margin: {{ $printFormat->margin_mm }}mm;
    }
}
```

### Font Size

Font size juga dinamis:

```blade
.nama-barang {
    font-size: {{ $printFormat->font_size_name }}px;
}

.kode-manual {
    font-size: {{ $printFormat->font_size_code }}px;
}
```

## 🧪 Testing

### Manual Testing

1. Pastikan database sudah di-migrate dan di-seed
2. Buka halaman detail asset
3. Coba setiap ukuran format:
   - 6x5 cm (default)
   - 5x3 cm
   - 5x2.5 cm
   - 5x2 cm
4. Verifikasi ukuran print sesuai dengan pilihan

### Test Cases

- ✅ Dropdown menampilkan semua format aktif
- ✅ Default format ter-select otomatis
- ✅ Print dengan format yang dipilih
- ✅ CSS dinamis sesuai format
- ✅ Auto-print berfungsi
- ✅ Fallback ke default jika format tidak valid

## 📊 Data Seeder

Default formats yang di-seed:

| Code | Name | Size | QR Size | Font Name | Font Code |
|------|------|------|---------|-----------|-----------|
| 6x5 | Stiker Besar | 6×5 cm | 120px | 11px | 10px |
| 5x3 | Stiker Sedang | 5×3 cm | 100px | 9px | 8px |
| 5x2.5 | Stiker Kecil | 5×2.5 cm | 90px | 8px | 7px |
| 5x2 | Stiker Mini | 5×2 cm | 80px | 7px | 6px |

## 🔍 Troubleshooting

### Format tidak muncul di dropdown
```bash
# Cek apakah seeder sudah dijalankan
php artisan db:seed --class=PrintFormatSeeder
```

### Print size tidak sesuai
- Pastikan browser print settings: "Scale" = 100%, "Margins" = None
- Cek printer settings untuk ukuran custom

### Error "No print format available"
```bash
# Jalankan seeder
php artisan db:seed --class=PrintFormatSeeder
```

## 🎯 Future Enhancements

Fitur yang bisa ditambahkan:

1. **CRUD Admin untuk Print Formats** - Manage format via admin panel
2. **User Preferences** - Simpan pilihan format per user
3. **Batch Print** - Print multiple QR codes sekaligus
4. **Custom Margin** - User bisa set margin sendiri
5. **Orientation** - Portrait/Landscape
6. **Multiple QR per Page** - Print beberapa QR dalam 1 halaman A4
7. **Export to PDF** - Download sebagai PDF

## 📝 Notes

- Ukuran dalam database disimpan dalam **cm** untuk kemudahan
- CSS menggunakan **mm** untuk print (1 cm = 10 mm)
- QR code size dalam **px** untuk rendering
- Font size dalam **px** untuk konsistensi

## ✅ Checklist Implementasi

- [x] Migration `print_formats` table
- [x] Model `PrintFormat` dengan helper methods
- [x] Seeder dengan 4 ukuran default
- [x] Update `DatabaseSeeder`
- [x] Update `QRCodeController@printableAsset`
- [x] Update view `printable-asset.blade.php` dengan dynamic CSS
- [x] Update component `qr-code.blade.php` dengan dropdown
- [x] JavaScript function `printQRCode()`
- [x] Dokumentasi lengkap

## 🎉 Selesai!

Fitur print QR code dengan pilihan ukuran sudah berhasil diimplementasikan!
