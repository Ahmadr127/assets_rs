# Analisis Refactoring - Print QR Code System

**Tanggal**: 2025-12-01  
**Status**: Sistem sudah menggunakan hardcoded templates untuk setiap ukuran

---

## 📋 Ringkasan

Sistem QR Code printing sudah beralih dari **dynamic template** (`printable-asset.blade.php`) ke **hardcoded templates** untuk setiap ukuran:
- `print-5x2.blade.php`
- `print-5x25.blade.php`
- `print-5x3.blade.php`
- `print-6x5.blade.php`

Akibatnya, beberapa komponen di `PrintFormat` model dan database sudah **tidak digunakan lagi**.

---

## ✅ Komponen yang MASIH DIGUNAKAN

### 1. **PrintFormat Model** (`app/Models/PrintFormat.php`)
**Method yang masih digunakan:**
- `getDefault()` - Untuk mendapatkan format default
- `getActive()` - Untuk dropdown pilihan format
- `scopeActive()` - Query scope
- `getDisplayNameAttribute()` - Untuk display name

**Kolom database yang masih digunakan:**
- `code` - Untuk mapping ke template (5x2, 5x3, dll)
- `name` - Untuk display name
- `width_cm` - Untuk informasi ukuran
- `height_cm` - Untuk informasi ukuran
- `is_active` - Untuk filter format aktif
- `is_default` - Untuk default selection
- `sort_order` - Untuk ordering di dropdown

### 2. **QRCodeController** (`app/Http/Controllers/QRCodeController.php`)
**Method `printableAsset()` masih menggunakan:**
```php
// Line 148-156: Query PrintFormat
$printFormat = \App\Models\PrintFormat::where('code', $formatCode)
    ->where('is_active', true)
    ->first();

// Line 191-198: Mapping code ke template
$templates = [
    '6x5' => 'qr-codes.print-6x5',
    '5x3' => 'qr-codes.print-5x3',
    '5x2.5' => 'qr-codes.print-5x25',
    '5x2' => 'qr-codes.print-5x2',
];
```

---

## ❌ Komponen yang TIDAK DIGUNAKAN LAGI

### 1. **PrintFormat Model - Method yang tidak terpakai**

```php
// ❌ TIDAK DIGUNAKAN - Bisa dihapus
public function getWidthMm(): float
public function getHeightMm(): float
public function getProportionalQrSizeMm(): float
public function getProportionalFontNameSize(): float
public function getProportionalFontCodeSize(): float
public function getProportionalMarginMm(): float
public function getLabelPaddingMm(): float
public function getContentGapMm(): float
public function getTextAreaWidthMm(): float
```

**Alasan**: Semua kalkulasi ukuran sudah hardcoded di masing-masing template.

### 2. **Database Columns yang tidak terpakai**

```sql
-- ❌ TIDAK DIGUNAKAN - Bisa dihapus
qr_size_px      -- Ukuran QR sudah hardcoded di template
margin_mm       -- Margin sudah hardcoded di template
font_size_name  -- Font size sudah hardcoded di template
font_size_code  -- Font size sudah hardcoded di template
description     -- Tidak digunakan di UI
```

**Kolom yang masih diperlukan:**
```sql
-- ✅ MASIH DIGUNAKAN
id, code, name, width_cm, height_cm, is_active, is_default, sort_order
```

### 3. **Template File yang tidak terpakai**

```
❌ resources/views/qr-codes/printable-asset.blade.php
```
**Alasan**: Sudah diganti dengan template spesifik per ukuran.

---

## 🎯 Rekomendasi Refactoring

### **Opsi 1: Minimal Cleanup (REKOMENDASI)**
Pertahankan struktur untuk fleksibilitas masa depan, hapus hanya yang benar-benar tidak digunakan.

**Action Items:**
1. ✅ **Hapus method yang tidak digunakan** di `PrintFormat` model
2. ✅ **Buat migration untuk drop columns** yang tidak digunakan
3. ✅ **Rename/Archive** `printable-asset.blade.php` → `printable-asset.blade.php.backup`
4. ✅ **Update seeder** untuk hanya insert kolom yang diperlukan

**Keuntungan:**
- Kode lebih clean dan mudah maintain
- Database lebih efisien
- Tetap fleksibel untuk perubahan masa depan

### **Opsi 2: Aggressive Cleanup**
Hapus semua yang tidak digunakan termasuk table `print_formats`.

**Action Items:**
1. ❌ Hapus model `PrintFormat` sepenuhnya
2. ❌ Drop table `print_formats`
3. ❌ Hardcode format mapping di controller

**Keuntungan:**
- Kode sangat minimal
- Tidak ada dependency ke database

**Kerugian:**
- ❌ Tidak fleksibel untuk menambah format baru
- ❌ Tidak ada UI untuk manage format
- ❌ Harus edit code untuk perubahan

---

## 📝 Implementation Plan (Opsi 1 - Rekomendasi)

### Step 1: Cleanup PrintFormat Model

```php
// app/Models/PrintFormat.php
// HAPUS semua method kalkulasi, pertahankan hanya:
// - getDefault()
// - getActive()
// - scopeActive()
// - getDisplayNameAttribute()
```

### Step 2: Create Migration untuk Drop Columns

```php
// database/migrations/2025_12_01_XXXXXX_cleanup_print_formats_table.php
Schema::table('print_formats', function (Blueprint $table) {
    $table->dropColumn([
        'qr_size_px',
        'margin_mm',
        'font_size_name',
        'font_size_code',
        'description'
    ]);
});
```

### Step 3: Update Seeder

```php
// database/seeders/PrintFormatSeeder.php
// Hapus kolom yang sudah di-drop dari insert data
```

### Step 4: Archive Old Template

```bash
mv resources/views/qr-codes/printable-asset.blade.php \
   resources/views/qr-codes/printable-asset.blade.php.backup
```

### Step 5: Update Documentation

Update semua dokumentasi untuk menjelaskan sistem baru menggunakan hardcoded templates.

---

## ⚠️ Catatan Penting

1. **Jangan hapus table `print_formats`** - Masih digunakan untuk:
   - Dropdown pilihan format di UI
   - Validasi format code
   - Default format selection

2. **Jangan hapus kolom `code`** - Digunakan untuk mapping ke template file

3. **Backup database** sebelum menjalankan migration drop columns

4. **Test semua format** setelah refactoring untuk memastikan tidak ada breaking changes

---

## 📊 Estimasi Impact

| Komponen | Before | After | Reduction |
|----------|--------|-------|-----------|
| PrintFormat Methods | 13 methods | 4 methods | -69% |
| Database Columns | 12 columns | 7 columns | -42% |
| Template Files | 5 files | 4 files (+ 1 backup) | -20% |
| Lines of Code | ~350 lines | ~150 lines | -57% |

---

## ✅ Checklist Sebelum Refactoring

- [ ] Backup database
- [ ] Backup semua file yang akan diubah
- [ ] Test semua format print (5x2, 5x2.5, 5x3, 6x5)
- [ ] Pastikan dropdown format masih berfungsi
- [ ] Pastikan default format masih berfungsi
- [ ] Review dengan tim
- [ ] Siapkan rollback plan

---

**Kesimpulan**: Sistem sudah tidak memerlukan dynamic calculation dari `PrintFormat` model karena semua ukuran sudah hardcoded di template masing-masing. Refactoring akan membuat kode lebih clean tanpa menghilangkan fleksibilitas untuk manage format melalui database.
