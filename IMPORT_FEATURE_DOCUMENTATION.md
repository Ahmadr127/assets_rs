# Dokumentasi Fitur Import Excel Fixed Assets

## Overview
Fitur import Excel memungkinkan pengguna untuk mengimport data Fixed Assets dalam jumlah besar dari file Excel dengan kemampuan:
- **Header Detection & Mapping**: Auto-detect kolom Excel dan mapping ke database
- **Data Validation**: Validasi data sebelum import
- **Duplicate Detection**: Deteksi duplikasi berdasarkan kode unik
- **Preview Data**: Preview data sebelum disimpan
- **Background Processing**: Import besar diproses di background menggunakan Queue
- **Error Handling**: Comprehensive error handling dan logging
- **Export Filtered Data**: Download data yang valid, duplikat, atau error

## Struktur Database

### Table: import_batches
Menyimpan informasi batch import:
- `id`: Primary key
- `user_id`: Foreign key ke users table
- `entity_type`: Tipe entity (default: fixed_assets)
- `original_filename`: Nama file asli
- `stored_filename`: Nama file di storage
- `total_rows`: Total baris data
- `processed_rows`: Baris yang sudah diproses
- `success_rows`: Baris yang berhasil diimport
- `failed_rows`: Baris yang gagal
- `duplicate_rows`: Baris yang duplikat
- `updated_rows`: Baris yang diupdate
- `status`: Status batch (pending, mapping, validating, processing, completed, failed, cancelled)
- `mapping_config`: Konfigurasi mapping kolom (JSON)
- `import_summary`: Summary hasil import (JSON)
- `validation_errors`: Error validasi (JSON)
- `started_at`: Waktu mulai processing
- `completed_at`: Waktu selesai processing

### Table: import_logs
Menyimpan log detail per baris:
- `id`: Primary key
- `import_batch_id`: Foreign key ke import_batches
- `row_index`: Nomor baris di Excel
- `row_data`: Data asli dari Excel (JSON)
- `mapped_data`: Data setelah mapping (JSON)
- `status`: Status baris (pending, valid, duplicate, error, skipped, imported, updated)
- `errors`: Error validasi (JSON)
- `duplicate_key`: Key yang menyebabkan duplikat
- `existing_record_id`: ID record yang sudah ada (jika duplikat)
- `processed_at`: Waktu diproses

## Flow Proses Import

```
1. Upload File
   ↓
2. Detect Headers (Auto-detect kolom Excel)
   ↓
3. Mapping Configuration (User mapping kolom ke database fields)
   ↓
4. Validation & Preview (Validasi data dan tampilkan preview)
   ↓
5. Process Import (Background job untuk import data)
   ↓
6. View Results (Lihat hasil import dengan statistik lengkap)
```

## Service Classes

### ExcelImportService
**Location**: `app/Services/ExcelImportService.php`

**Methods**:
- `detectHeaders(ImportBatch $batch)`: Deteksi header dari file Excel
- `suggestDatabaseField(string $excelHeader)`: Suggest field database berdasarkan nama kolom Excel
- `readAndMapData(ImportBatch $batch)`: Baca dan mapping data dari Excel
- `formatValue($value, string $field)`: Format value sesuai tipe field
- `validateAndFilterData(ImportBatch $batch, array $data)`: Validasi dan filter data
- `processImport(ImportBatch $batch, array $validData, string $action)`: Proses import ke database

### DataFilterService
**Location**: `app/Services/DataFilterService.php`

**Methods**:
- `validateRequiredFields(array $data)`: Validasi field wajib
- `checkDuplicate(array $data)`: Cek duplikasi di database
- `findDuplicates(array $data, array $uniqueKeys)`: Cari duplikasi dalam batch
- `flagExistingRecords(array $data)`: Flag record yang sudah ada
- `generateImportSummary(array $validatedData)`: Generate summary statistik
- `resolveForeignKeys(array $data)`: Resolve foreign keys dari text ke ID
- `validateDataFormat(array $data)`: Validasi format data

### ImportBatchService
**Location**: `app/Services/ImportBatchService.php`

**Methods**:
- `createImportBatch(UploadedFile $file, int $userId, string $entityType)`: Buat batch baru
- `updateBatchStatus(ImportBatch $batch, string $status, ?array $summary)`: Update status batch
- `saveMappingConfig(ImportBatch $batch, array $mappingConfig)`: Simpan konfigurasi mapping
- `logImportResults(ImportBatch $batch, array $results)`: Log hasil import
- `saveValidationResults(ImportBatch $batch, array $validatedData)`: Simpan hasil validasi
- `getBatchStatistics(ImportBatch $batch)`: Get statistik batch
- `deleteBatch(ImportBatch $batch)`: Hapus batch dan file
- `cancelBatch(ImportBatch $batch)`: Cancel batch
- `retryBatch(ImportBatch $batch)`: Retry batch yang gagal

## Job: ProcessExcelImport
**Location**: `app/Jobs/ProcessExcelImport.php`

Background job untuk memproses import data. Dijalankan menggunakan Laravel Queue.

**Properties**:
- `$timeout = 600`: Timeout 10 menit
- `$tries = 3`: Retry hingga 3 kali jika gagal

**Flow**:
1. Update status ke 'processing'
2. Read dan map data dari Excel
3. Validasi dan filter data
4. Simpan hasil validasi ke logs
5. Proses data valid ke database
6. Update batch statistics
7. Handle error jika ada

## Controller: ExcelImportController
**Location**: `app/Http/Controllers/ExcelImportController.php`

**Routes & Methods**:

| Method | Route | Action | Description |
|--------|-------|--------|-------------|
| GET | `/imports` | index | List semua batch import |
| GET | `/imports/create` | create | Form upload file |
| POST | `/imports/upload` | uploadAndDetect | Upload file dan detect headers |
| GET | `/imports/{batch}/mapping` | showMapping | Tampilkan form mapping |
| POST | `/imports/{batch}/configure-mapping` | configureMapping | Simpan konfigurasi mapping |
| GET | `/imports/{batch}/preview` | preview | Preview data sebelum import |
| POST | `/imports/{batch}/process` | processImport | Proses import (dispatch job) |
| GET | `/imports/{batch}/progress` | progress | Tampilkan progress import |
| GET | `/imports/{batch}/progress-data` | getProgress | Get progress data (AJAX) |
| GET | `/imports/{batch}/download-filtered` | downloadFilteredData | Download filtered data |
| GET | `/imports/{batch}` | show | Detail batch import |
| DELETE | `/imports/{batch}` | destroy | Hapus batch |
| POST | `/imports/{batch}/cancel` | cancel | Cancel batch |
| POST | `/imports/{batch}/retry` | retry | Retry batch yang gagal |

## Request Validation

### ExcelUploadRequest
Validasi upload file:
- `file`: required, file, mimes:xlsx,xls,csv, max:10MB
- `entity_type`: nullable, string, in:fixed_assets

### MappingConfigurationRequest
Validasi konfigurasi mapping:
- `mapping`: required, array
- `mapping.*`: nullable, string

### ImportConfirmationRequest
Validasi konfirmasi import:
- `action`: required, string, in:create,update,skip
- `process_duplicates`: nullable, boolean
- `process_errors`: nullable, boolean

## Views

### imports/index.blade.php
List semua batch import dengan statistik dan aksi.

### imports/upload.blade.php
Form upload file Excel dengan panduan.

### imports/mapping.blade.php
Form mapping kolom Excel ke database fields dengan auto-suggestion.

### imports/preview.blade.php
Preview data dengan tabs untuk:
- Valid data (siap diimport)
- Duplicates (data yang sudah ada)
- Errors (data dengan error validasi)

Menampilkan summary statistics dan opsi untuk download filtered data.

### imports/progress.blade.php
Tampilan progress import dengan:
- Progress bar real-time
- Statistics (total, success, failed, duplicates)
- Auto-refresh menggunakan AJAX

### imports/show.blade.php
Detail lengkap batch import dengan:
- Informasi batch
- Import summary
- Logs per baris dengan status dan error
- Opsi download filtered data
- Aksi retry atau delete

## Export: ImportLogsExport
**Location**: `app/Exports/ImportLogsExport.php`

Export logs ke Excel dengan kolom:
- Row Index
- Status
- Kode, Nama, Tipe, dll (data fields)
- Errors
- Duplicate Key

## Cara Penggunaan

### 1. Upload File Excel
```
1. Akses menu "Import Data" atau route /imports
2. Klik "Upload File Baru"
3. Pilih file Excel (.xlsx, .xls, atau .csv)
4. Klik "Upload dan Lanjutkan"
```

### 2. Mapping Kolom
```
1. Sistem akan auto-detect header dari Excel
2. Review dan sesuaikan mapping kolom ke database fields
3. Kolom yang tidak diperlukan bisa di-skip
4. Pastikan field wajib sudah dimapping:
   - Kode (unique identifier)
   - Nama Fixed Asset
   - Taksiran Umur
   - Efektif Mulai
   - PIC
5. Klik "Lanjut ke Preview"
```

### 3. Preview Data
```
1. Review data yang akan diimport
2. Cek tab "Valid Data" untuk data yang siap diimport
3. Cek tab "Duplicates" untuk data yang sudah ada
4. Cek tab "Errors" untuk data dengan error validasi
5. Download filtered data jika perlu untuk review
6. Pilih aksi:
   - Create: Buat data baru (skip duplicates)
   - Update: Update data yang duplikat
7. Klik "Proses Import"
```

### 4. Monitor Progress
```
1. Sistem akan redirect ke halaman progress
2. Progress bar akan update secara real-time
3. Tunggu hingga status "COMPLETED"
4. Klik "Lihat Detail Hasil" untuk melihat hasil lengkap
```

### 5. Review Hasil
```
1. Lihat statistik import (success, failed, duplicates)
2. Review logs per baris
3. Download data error untuk perbaikan
4. Jika ada error, bisa retry atau upload file baru
```

## Format File Excel

### Header Row (Baris 1)
File Excel harus memiliki header di baris pertama. Contoh:

```
| Kode | Nama Fixed Asset | Tipe | Taksiran Umur | Nilai Awal | Efektif Mulai | Lokasi | Status | Kondisi | Vendor | Brand | PIC |
```

### Field Wajib
- **Kode**: Unique identifier (akan dicek duplikasi)
- **Nama Fixed Asset**: Nama asset
- **Taksiran Umur**: Umur dalam tahun (angka)
- **Efektif Mulai**: Tanggal mulai (YYYY-MM-DD atau DD/MM/YYYY)
- **PIC**: Person in Charge

### Field Opsional
- Kode Manual
- Tipe Fixed Asset
- Nilai Awal (numeric)
- Deskripsi
- Lokasi
- Status (aktif, tidak_aktif, maintenance, rusak)
- Kondisi (baik, rusak_ringan, rusak_berat, tidak_layak)
- Vendor
- Brand
- Code Type
- Serial Number
- Harus Dicek Fisik (boolean)

### Contoh Data
```
FA001 | Laptop Dell | Elektronik | 5 | 15000000 | 2024-01-01 | Kantor Pusat | aktif | baik | PT Dell | Dell | John Doe
FA002 | Meja Kerja | Furniture | 10 | 2000000 | 2024-01-15 | Kantor Cabang | aktif | baik | PT Furniture | IKEA | Jane Smith
```

## Foreign Key Resolution

Sistem akan otomatis membuat master data jika belum ada:
- **Lokasi**: Jika lokasi belum ada, akan dibuat otomatis
- **Status**: Jika status belum ada, akan dibuat otomatis
- **Kondisi**: Jika kondisi belum ada, akan dibuat otomatis
- **Vendor**: Jika vendor belum ada, akan dibuat otomatis
- **Brand**: Jika brand belum ada, akan dibuat otomatis
- **Tipe Asset**: Jika tipe belum ada, akan dibuat otomatis

## Queue Configuration

Untuk processing background, pastikan queue worker berjalan:

```bash
# Development
php artisan queue:work

# Production (dengan supervisor)
php artisan queue:work --tries=3 --timeout=600
```

Atau gunakan command `dev` yang sudah dikonfigurasi:
```bash
composer dev
```

## Error Handling

### Common Errors

1. **File too large**: Maksimal 10MB
   - Solution: Split file menjadi beberapa bagian

2. **Invalid file format**: Hanya .xlsx, .xls, .csv
   - Solution: Convert file ke format yang didukung

3. **Missing required fields**: Field wajib tidak diisi
   - Solution: Lengkapi data atau mapping ulang kolom

4. **Duplicate kode**: Kode sudah ada di database
   - Solution: Pilih action "Update" atau ubah kode

5. **Invalid date format**: Format tanggal tidak valid
   - Solution: Gunakan format YYYY-MM-DD atau DD/MM/YYYY

6. **Invalid numeric value**: Nilai bukan angka
   - Solution: Pastikan field numeric berisi angka

## Testing

### Manual Testing Checklist

- [ ] Upload file Excel valid
- [ ] Upload file dengan format salah
- [ ] Upload file > 10MB
- [ ] Auto-detect headers
- [ ] Mapping kolom manual
- [ ] Preview data valid
- [ ] Preview data dengan error
- [ ] Preview data duplikat
- [ ] Process import (create)
- [ ] Process import (update)
- [ ] Monitor progress real-time
- [ ] View hasil import
- [ ] Download filtered data (valid, errors, duplicates)
- [ ] Retry failed batch
- [ ] Delete batch
- [ ] Cancel batch

### Sample Test Data

Gunakan file sample di `sampleExcel/FA TERAMEDIK-SEPTEMBER 2025.xlsx` untuk testing.

## Troubleshooting

### Import stuck di "Processing"
1. Cek queue worker masih running
2. Cek log di `storage/logs/laravel.log`
3. Restart queue worker

### Memory limit exceeded
1. Increase PHP memory limit di `php.ini`
2. Split file menjadi batch lebih kecil
3. Gunakan chunk processing

### Timeout
1. Increase timeout di Job (default 600s)
2. Increase PHP max_execution_time
3. Process file lebih kecil

## Performance Tips

1. **Batch Size**: Import maksimal 1000 rows per batch untuk performa optimal
2. **Queue**: Gunakan Redis atau database queue untuk reliability
3. **Indexing**: Database sudah diindex untuk performa query
4. **Chunk Reading**: File dibaca per chunk untuk memory efficiency
5. **Background Processing**: Import besar otomatis diproses di background

## Security

1. **File Validation**: Hanya file Excel/CSV yang diizinkan
2. **Size Limit**: Maksimal 10MB per file
3. **User Authorization**: Hanya user dengan permission `manage_fixed_assets`
4. **Batch Ownership**: User hanya bisa akses batch miliknya sendiri
5. **SQL Injection Prevention**: Menggunakan Eloquent ORM dan prepared statements

## Future Enhancements

- [ ] Support multiple entity types (not just fixed_assets)
- [ ] Template generator dengan sample data
- [ ] Bulk update via Excel
- [ ] Import scheduling
- [ ] Email notification saat import selesai
- [ ] Import from Google Sheets
- [ ] Advanced mapping dengan formula
- [ ] Data transformation rules
- [ ] Import history analytics
- [ ] Rollback capability

## Support

Untuk pertanyaan atau issue, hubungi tim development atau buat issue di repository.
