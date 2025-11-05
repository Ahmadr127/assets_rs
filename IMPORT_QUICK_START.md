# Quick Start Guide - Import Excel Fixed Assets

## Setup (One Time)

### 1. Install Dependencies
```bash
composer install
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Start Queue Worker
```bash
# Option 1: Manual
php artisan queue:work

# Option 2: Using composer script (includes server + queue + vite)
composer dev
```

## Usage Flow

### Step 1: Prepare Excel File
Pastikan file Excel memiliki:
- Header di baris pertama
- Kolom wajib: Kode, Nama Fixed Asset, Taksiran Umur, Efektif Mulai, PIC
- Format: .xlsx, .xls, atau .csv
- Ukuran maksimal: 10MB

**Contoh Header:**
```
Kode | Nama Fixed Asset | Tipe | Taksiran Umur | Nilai Awal | Efektif Mulai | Lokasi | Status | Kondisi | PIC
```

### Step 2: Upload File
1. Login ke aplikasi
2. Akses menu **Import Data** atau URL: `/imports`
3. Klik **Upload File Baru**
4. Pilih file Excel
5. Klik **Upload dan Lanjutkan**

### Step 3: Mapping Kolom
1. Sistem akan auto-detect header dari Excel
2. Review mapping yang disarankan (ditandai dengan ✓ Auto-detected)
3. Sesuaikan mapping jika perlu
4. Pastikan field wajib sudah dimapping (ditandai dengan "Required")
5. Klik **Lanjut ke Preview**

### Step 4: Preview & Validasi
1. Review **Summary Statistics**:
   - Total Rows
   - Valid Data (hijau)
   - Duplicates (kuning)
   - Errors (merah)

2. Cek tab **Valid Data**:
   - Data yang siap diimport
   - Download jika perlu review

3. Cek tab **Duplicates**:
   - Data yang sudah ada di database
   - Lihat existing record ID
   - Download untuk review

4. Cek tab **Errors**:
   - Data dengan error validasi
   - Lihat pesan error per field
   - Download untuk perbaikan

5. Pilih aksi:
   - **Create**: Buat data baru (skip duplicates)
   - **Update**: Update data yang duplikat

6. Klik **Proses Import**

### Step 5: Monitor Progress
1. Sistem akan redirect ke halaman progress
2. Progress bar update otomatis setiap 3 detik
3. Lihat statistik real-time:
   - Total Rows
   - Success (hijau)
   - Failed (merah)
   - Duplicates (kuning)
4. Tunggu hingga status **COMPLETED**

### Step 6: Review Hasil
1. Klik **Lihat Detail Hasil**
2. Review statistik final:
   - Success Rate
   - Total processed
   - Duration
3. Lihat logs per baris dengan status dan error
4. Download filtered data jika perlu:
   - Valid data
   - Duplicates
   - Errors

## Common Scenarios

### Scenario 1: Import Data Baru (No Duplicates)
```
Upload → Mapping → Preview (semua valid) → Process (Create) → Done ✓
```

### Scenario 2: Import dengan Duplikasi
```
Upload → Mapping → Preview (ada duplicates) → 
Pilih Action:
  - Create: Skip duplicates, import yang baru saja
  - Update: Update data yang duplikat
→ Process → Done ✓
```

### Scenario 3: Import dengan Error
```
Upload → Mapping → Preview (ada errors) → 
Download error data → Perbaiki di Excel → 
Upload ulang → Done ✓
```

### Scenario 4: Import Gagal (Failed)
```
Upload → Mapping → Preview → Process → Failed ✗
→ Lihat error di detail
→ Klik "Retry Import" atau upload file baru
```

## Tips & Best Practices

### ✅ DO's
- Gunakan template Excel yang konsisten
- Pastikan format tanggal: YYYY-MM-DD atau DD/MM/YYYY
- Gunakan kode unik untuk setiap asset
- Review preview sebelum import
- Download error data untuk perbaikan
- Import maksimal 1000 rows per batch untuk performa optimal

### ❌ DON'Ts
- Jangan upload file > 10MB
- Jangan gunakan format selain .xlsx, .xls, .csv
- Jangan kosongkan field wajib
- Jangan gunakan kode duplikat tanpa pilih action "Update"
- Jangan close browser saat processing (biarkan background job selesai)

## Troubleshooting

### Problem: Upload gagal
**Solution:**
- Cek ukuran file (max 10MB)
- Cek format file (.xlsx, .xls, .csv)
- Cek koneksi internet

### Problem: Banyak error saat preview
**Solution:**
- Download error data
- Cek field wajib sudah diisi
- Cek format tanggal dan angka
- Perbaiki di Excel dan upload ulang

### Problem: Import stuck di "Processing"
**Solution:**
- Cek queue worker masih running: `php artisan queue:work`
- Refresh halaman setelah beberapa menit
- Cek log: `storage/logs/laravel.log`

### Problem: Duplicate data
**Solution:**
- Pilih action "Update" untuk update data existing
- Atau ubah kode di Excel agar unique

## Keyboard Shortcuts

- `Ctrl + Click` pada batch: Open in new tab
- `F5`: Refresh progress
- `Esc`: Close modal/alert

## API Endpoints (untuk integrasi)

```php
GET    /imports                          // List batches
POST   /imports/upload                   // Upload file
GET    /imports/{batch}/progress-data    // Get progress (JSON)
GET    /imports/{batch}/download-filtered?type=errors  // Download filtered
```

## Sample Excel Template

Download template: `/templates/fixed_assets_import_template.xlsx`

Atau gunakan sample: `sampleExcel/FA TERAMEDIK-SEPTEMBER 2025.xlsx`

## Need Help?

- 📖 Full Documentation: `IMPORT_FEATURE_DOCUMENTATION.md`
- 🐛 Report Bug: Create issue di repository
- 💬 Questions: Contact development team

---

**Happy Importing! 🚀**
