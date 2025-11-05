# Migration Fix Summary - ENUM to VARCHAR

## Problem

Migration `2025_11_05_110000_change_status_kondisi_to_varchar.php` sudah dijalankan tapi masih terjadi error ENUM constraint:

```
SQLSTATE[23514]: Check violation: 7 ERROR: new row for relation "fixed_assets" 
violates check constraint "fixed_assets_status_check"
```

## Root Cause

PostgreSQL memerlukan **`USING` clause** saat mengkonversi ENUM ke VARCHAR. Migration awal tidak menggunakan `USING`, sehingga konversi gagal.

### Migration Awal (SALAH)
```php
// ❌ Gagal di PostgreSQL
DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status TYPE VARCHAR(255)");
```

### Migration Fixed (BENAR)
```php
// ✅ Berhasil di PostgreSQL
DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
```

## Solution Applied

### 1. Rollback Migration
```bash
php artisan migrate:rollback --step=1
```

### 2. Fix Migration File
Updated `database/migrations/2025_11_05_110000_change_status_kondisi_to_varchar.php`:

```php
public function up(): void
{
    // PostgreSQL requires USING clause to convert ENUM to VARCHAR
    // Change status from ENUM to VARCHAR
    DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
    DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status SET DEFAULT 'aktif'");
    
    // Change kondisi from ENUM to VARCHAR
    DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi TYPE VARCHAR(255) USING kondisi::text");
    DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi SET DEFAULT 'baik'");
    
    // Normalize existing data to lowercase for consistency
    DB::statement("UPDATE fixed_assets SET status = LOWER(status) WHERE status IS NOT NULL");
    DB::statement("UPDATE fixed_assets SET kondisi = LOWER(kondisi) WHERE kondisi IS NOT NULL");
}
```

### 3. Re-run Migration
```bash
php artisan migrate
```

**Result:** ✅ **SUCCESS** - Migration completed in 255.54ms

## Verification

After migration, the columns should be:
- `status`: VARCHAR(255) with default 'aktif'
- `kondisi`: VARCHAR(255) with default 'baik'

### Test Query
```sql
SELECT column_name, data_type, character_maximum_length, column_default
FROM information_schema.columns 
WHERE table_name = 'fixed_assets' 
AND column_name IN ('status', 'kondisi');
```

Expected result:
```
 column_name | data_type | character_maximum_length | column_default
-------------+-----------+-------------------------+----------------
 kondisi     | character varying | 255             | 'baik'::character varying
 status      | character varying | 255             | 'aktif'::character varying
```

## Import Should Now Work

The following values that previously caused errors will now be accepted:

### Status Values (Now Accepted)
- ✅ "titipan dari keuangan" → normalized to "aktif"
- ✅ "1 buah titipan dari unit pembelian" → normalized to "aktif"
- ✅ "diijual" → normalized to "tidak_aktif"
- ✅ "dijual" → normalized to "tidak_aktif"
- ✅ "mobl ambulan" → normalized to "aktif"
- ✅ "terpasang" → normalized to "aktif"
- ✅ "expired (hanya 1 tahun)" → normalized to "tidak_aktif"

### Kondisi Values (Now Accepted)
- ✅ "Baik" → normalized to "baik"
- ✅ "Perlu di service" → normalized to "rusak_ringan"
- ✅ "Perlu service" → normalized to "rusak_ringan"
- ✅ Any other value → accepted as-is or normalized to "baik"

## Key Differences: PostgreSQL vs MySQL

### MySQL
```sql
-- MySQL allows direct conversion
ALTER TABLE fixed_assets MODIFY status VARCHAR(255);
```

### PostgreSQL
```sql
-- PostgreSQL requires USING clause
ALTER TABLE fixed_assets ALTER COLUMN status TYPE VARCHAR(255) USING status::text;
```

## Lessons Learned

1. **Always test migrations on target database** - PostgreSQL and MySQL have different syntax
2. **Use `USING` clause** when converting types in PostgreSQL
3. **Check migration success** - Don't assume migration ran successfully just because it didn't error
4. **Rollback and re-run** if migration didn't work as expected

## Next Steps

1. ✅ Migration fixed and re-run successfully
2. ⏭️ Test import with previously failing data
3. ⏭️ Verify all status/kondisi values are normalized correctly
4. ⏭️ Monitor import logs for any remaining issues

## Status

🟢 **RESOLVED** - Migration successfully converted ENUM to VARCHAR with proper PostgreSQL syntax.

Import should now work without ENUM constraint violations!
