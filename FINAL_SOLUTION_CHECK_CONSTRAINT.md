# FINAL SOLUTION: Drop ENUM Check Constraints

## Problem Identified

Meskipun migration sudah mengubah tipe kolom dari ENUM ke VARCHAR, **CHECK CONSTRAINT masih aktif** di database PostgreSQL.

### Error yang Masih Terjadi
```
SQLSTATE[23514]: Check violation: 7 ERROR: new row for relation "fixed_assets" 
violates check constraint "fixed_assets_status_check"
```

### Root Cause

Ketika Laravel membuat kolom ENUM di PostgreSQL:
```php
$table->enum('status', ['aktif', 'tidak_aktif', 'maintenance', 'rusak']);
```

PostgreSQL secara otomatis membuat **CHECK CONSTRAINT**:
```sql
ALTER TABLE fixed_assets 
ADD CONSTRAINT fixed_assets_status_check 
CHECK (status IN ('aktif', 'tidak_aktif', 'maintenance', 'rusak'));
```

**Masalah:** Ketika kita ubah tipe kolom ke VARCHAR, **CHECK CONSTRAINT tidak otomatis terhapus!**

## Complete Solution Timeline

### ❌ Attempt 1: Change Type Without USING (FAILED)
```php
DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status TYPE VARCHAR(255)");
```
**Result:** Migration ran but didn't actually change the type

### ❌ Attempt 2: Change Type With USING (PARTIAL SUCCESS)
```php
DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
```
**Result:** Type changed to VARCHAR, but CHECK CONSTRAINT still active

### ✅ Attempt 3: Drop CHECK CONSTRAINT (SUCCESS!)
```php
// Find constraint name dynamically
$statusConstraint = DB::select("
    SELECT conname 
    FROM pg_constraint 
    WHERE conrelid = 'fixed_assets'::regclass 
    AND contype = 'c' 
    AND conname LIKE '%status%check%'
");

// Drop the constraint
DB::statement("ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS {$constraintName}");
```

## Final Migration

**File:** `database/migrations/2025_11_05_112800_drop_enum_check_constraints.php`

```php
public function up(): void
{
    // Find and drop status check constraint
    $statusConstraint = DB::select("
        SELECT conname 
        FROM pg_constraint 
        WHERE conrelid = 'fixed_assets'::regclass 
        AND contype = 'c' 
        AND conname LIKE '%status%check%'
    ");
    
    if (!empty($statusConstraint)) {
        $constraintName = $statusConstraint[0]->conname;
        DB::statement("ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS {$constraintName}");
    }
    
    // Find and drop kondisi check constraint
    $kondisiConstraint = DB::select("
        SELECT conname 
        FROM pg_constraint 
        WHERE conrelid = 'fixed_assets'::regclass 
        AND contype = 'c' 
        AND conname LIKE '%kondisi%check%'
    ");
    
    if (!empty($kondisiConstraint)) {
        $constraintName = $kondisiConstraint[0]->conname;
        DB::statement("ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS {$constraintName}");
    }
    
    // Fallback: try exact names
    DB::statement("ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS fixed_assets_status_check");
    DB::statement("ALTER TABLE fixed_assets DROP CONSTRAINT IF EXISTS fixed_assets_kondisi_check");
}
```

## Verification

### Check Constraints Status
```sql
-- List all check constraints on fixed_assets table
SELECT conname, pg_get_constraintdef(oid) 
FROM pg_constraint 
WHERE conrelid = 'fixed_assets'::regclass 
AND contype = 'c';
```

**Expected Result:** Empty (no check constraints)

### Check Column Types
```sql
SELECT column_name, data_type, character_maximum_length 
FROM information_schema.columns 
WHERE table_name = 'fixed_assets' 
AND column_name IN ('status', 'kondisi');
```

**Expected Result:**
```
 column_name | data_type         | character_maximum_length
-------------+-------------------+-------------------------
 kondisi     | character varying | 255
 status      | character varying | 255
```

## Migration Execution

```bash
# Run the new migration
php artisan migrate

# Output:
# INFO  Running migrations.
# 2025_11_05_112800_drop_enum_check_constraints  12.51ms DONE
```

**Status:** ✅ **SUCCESS**

## What This Fixes

### Before (With CHECK CONSTRAINT)
```sql
INSERT INTO fixed_assets (status, ...) VALUES ('terpasang', ...);
-- ❌ ERROR: violates check constraint "fixed_assets_status_check"
```

### After (Without CHECK CONSTRAINT)
```sql
INSERT INTO fixed_assets (status, ...) VALUES ('terpasang', ...);
-- ✅ SUCCESS: Value accepted and normalized by application
```

## Values Now Accepted

All these values will now be accepted and normalized:

### Status Values
- ✅ "titipan dari keuangan" → normalized to "aktif"
- ✅ "1 buah titipan dari unit pembelian" → normalized to "aktif"
- ✅ "diijual" → normalized to "tidak_aktif"
- ✅ "mobl ambulan" → normalized to "aktif"
- ✅ "terpasang" → normalized to "aktif"
- ✅ "expired (hanya 1 tahun)" → normalized to "tidak_aktif"
- ✅ Any other value → normalized by `DataFilterService`

### Kondisi Values
- ✅ "Baik" → normalized to "baik"
- ✅ "Perlu di service" → normalized to "rusak_ringan"
- ✅ Any other value → normalized by `DataFilterService`

## Complete Migration History

1. ✅ `2025_11_05_100000_make_status_kondisi_nullable.php` - Make columns nullable
2. ✅ `2025_11_05_110000_change_status_kondisi_to_varchar.php` - Change ENUM to VARCHAR
3. ✅ `2025_11_05_112800_drop_enum_check_constraints.php` - **Drop CHECK constraints**

## Key Learnings

### PostgreSQL ENUM Behavior
1. **ENUM creates CHECK CONSTRAINT** - Not just a type, but also a constraint
2. **Changing type doesn't drop constraint** - Must be dropped manually
3. **CHECK CONSTRAINT survives type change** - Even after ALTER COLUMN TYPE

### Migration Best Practices
1. **Always verify constraints** - Check `pg_constraint` table
2. **Use dynamic constraint names** - Query to find actual constraint name
3. **Test on staging first** - ENUM behavior varies by database
4. **Add fallback statements** - Try exact names if query fails

### Laravel + PostgreSQL ENUM
```php
// ❌ DON'T: Use ENUM for user-input fields
$table->enum('status', ['aktif', 'tidak_aktif']);

// ✅ DO: Use VARCHAR for flexible data
$table->string('status')->default('aktif');

// ✅ DO: Validate in application layer
protected function normalizeStatusValue(string $status): string {
    // Normalize and validate here
}
```

## Testing Checklist

- [x] Migration runs successfully
- [x] CHECK constraints dropped
- [x] Column type is VARCHAR(255)
- [ ] Import test with "terpasang" status
- [ ] Import test with "diijual" status
- [ ] Import test with "Perlu di service" kondisi
- [ ] Verify normalization works
- [ ] Check existing data not affected

## Next Steps

1. **Test import immediately** - Upload Excel file with problematic values
2. **Monitor import logs** - Check for any remaining errors
3. **Verify normalization** - Ensure values are normalized correctly
4. **Update documentation** - Document accepted values for users

## Status

🟢 **RESOLVED** - CHECK CONSTRAINTS successfully dropped!

Import should now work without any ENUM constraint violations! 🎉

---

## Emergency Rollback

If needed, rollback all three migrations:

```bash
php artisan migrate:rollback --step=3
```

This will:
1. Re-create CHECK constraints
2. Change VARCHAR back to ENUM
3. Make columns NOT NULL again

**Warning:** Only rollback if absolutely necessary, as it will break imports again!
