# Quick Start - Print Format Feature

## 🚀 Langkah-langkah Setup

### 1. Pastikan Database Sudah Running

Cek apakah PostgreSQL sudah running:
```bash
# Windows
# Buka Services dan cari PostgreSQL
# Atau jalankan:
pg_ctl status
```

### 2. Jalankan Migration

```bash
php artisan migrate
```

**Expected Output:**
```
Migrating: 2025_12_01_000001_create_print_formats_table
Migrated:  2025_12_01_000001_create_print_formats_table (XX.XXms)
```

### 3. Jalankan Seeder

```bash
php artisan db:seed --class=PrintFormatSeeder
```

**Expected Output:**
```
Print formats seeded successfully!
Available formats: 6x5 (default), 5x3, 5x2.5, 5x2
```

### 4. Verifikasi Data

```bash
php artisan tinker
```

Kemudian jalankan:
```php
\App\Models\PrintFormat::all();
// Harus menampilkan 4 records

\App\Models\PrintFormat::getDefault();
// Harus menampilkan format 6x5
```

## ✅ Testing

1. Buka halaman detail asset
2. Lihat QR code component
3. Dropdown "Ukuran Label" harus menampilkan 4 pilihan
4. Pilih ukuran dan klik Print
5. Verifikasi ukuran label sesuai pilihan

## 🔧 Jika Ada Error

### Error: "could not connect to server"
**Solusi:** Start PostgreSQL service

### Error: "Table 'print_formats' doesn't exist"
**Solusi:** Jalankan migration
```bash
php artisan migrate
```

### Error: "No print format available"
**Solusi:** Jalankan seeder
```bash
php artisan db:seed --class=PrintFormatSeeder
```

### Dropdown tidak muncul
**Solusi:** Clear cache
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

## 📋 Checklist

- [ ] Database PostgreSQL running
- [ ] Migration berhasil dijalankan
- [ ] Seeder berhasil dijalankan
- [ ] Verifikasi data di database
- [ ] Test print dengan berbagai ukuran
- [ ] Verifikasi ukuran print sesuai

## 🎯 Next Steps

Setelah setup berhasil, Anda bisa:

1. **Test semua ukuran print** (6x5, 5x3, 5x2.5, 5x2)
2. **Customize format** jika perlu via database
3. **Tambah format baru** jika diperlukan
4. **Buat CRUD admin** untuk manage format (optional)

---

**Need Help?** Lihat file `PRINT_FORMAT_IMPLEMENTATION.md` untuk dokumentasi lengkap.
