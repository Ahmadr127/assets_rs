# Import Fixed Assets - Bug Fixes Documentation

## Issue Summary

**Error:** `SQLSTATE[25P02]: In failed sql transaction: 7 ERROR: current transaction is aborted, commands ignored until end of transaction block`

**Root Cause:** The `status` and `kondisi` columns in the `fixed_assets` table were defined as NOT NULL ENUMs with default values, but the import logic was passing `null` values, causing constraint violations.

## Problems Identified

### 1. Database Schema Issues

**Location:** `database/migrations/2025_10_07_024735_create_fixed_assets_table.php`

```php
$table->enum('status', ['aktif', 'tidak_aktif', 'maintenance', 'rusak'])->default('aktif');
$table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'tidak_layak'])->default('baik');
```

- Both fields were NOT NULL with defaults
- Migration `2025_11_05_035919_make_fixed_assets_fields_nullable.php` made other fields nullable but **forgot** `status` and `kondisi`
- PostgreSQL requires explicit values or DEFAULT keyword when inserting

### 2. Import Logic Issues

**Location:** `app/Services/DataFilterService.php` (lines 240-262)

```php
// Old code - problematic
if (isset($data['status']) && !isset($data['status_id'])) {
    // Only resolves if status is set
}
```

**Problem:** `isset()` returns `false` for `null` values, so:
- When Excel cell is empty → `status` = `null`
- `isset($data['status'])` = `false`
- No default value is set
- Insert fails with NOT NULL constraint violation

### 3. Transaction Handling Issues

**Location:** `app/Services/ExcelImportService.php` (lines 265-364)

**Old approach:**
```php
DB::beginTransaction();
foreach ($validData as $item) {
    try {
        // Insert fixed_asset (fails here)
        // Insert import_log (fails because transaction is aborted)
    } catch (Exception $e) {
        // Exception caught but transaction still aborted
    }
}
DB::commit(); // Never reached
```

**Problem:** When first insert fails:
1. PostgreSQL aborts the transaction
2. All subsequent queries fail with "transaction is aborted" error
3. Import logs cannot be saved
4. No proper error tracking

## Solutions Implemented

### Fix 1: Database Migration

**File:** `database/migrations/2025_11_05_100000_make_status_kondisi_nullable.php`

```php
// Make status and kondisi nullable while keeping defaults
DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status DROP NOT NULL");
DB::statement("ALTER TABLE fixed_assets ALTER COLUMN status SET DEFAULT 'aktif'");

DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi DROP NOT NULL");
DB::statement("ALTER TABLE fixed_assets ALTER COLUMN kondisi SET DEFAULT 'baik'");
```

**Benefits:**
- Allows NULL values during import
- Maintains default values for new records
- Backward compatible with existing data

### Fix 2: Default Value Handling

**File:** `app/Services/DataFilterService.php` (lines 240-262)

```php
// New code - fixed
if (!empty($data['status']) && !isset($data['status_id'])) {
    // Resolve to status_id
} elseif (empty($data['status']) && !isset($data['status_id'])) {
    // Set default value
    $resolved['status'] = 'aktif';
}
```

**Changes:**
- Changed `isset()` to `!empty()` to properly detect null/empty values
- Added `elseif` clause to set default values when field is empty
- Applied same logic to both `status` and `kondisi` fields
- Applied to all foreign key resolution logic

### Fix 3: Transaction Isolation

**File:** `app/Services/ExcelImportService.php` (lines 265-368)

```php
// New approach - individual transactions per row
foreach ($validData as $item) {
    DB::beginTransaction();
    try {
        // Insert fixed_asset
        // Insert import_log
        DB::commit();
    } catch (Exception $e) {
        DB::rollBack();
        
        // Log error in separate transaction
        DB::beginTransaction();
        try {
            ImportLog::create([...]);
            DB::commit();
        } catch (Exception $logError) {
            DB::rollBack();
        }
    }
}
```

**Benefits:**
- Each row has its own transaction
- One row failure doesn't affect others
- Error logs are saved in separate transactions
- Prevents cascading failures
- Better error tracking and recovery

### Fix 4: Model Validation Update

**File:** `app/Models/FixedAsset.php` (line 67)

```php
// Changed from 'boolean' to 'nullable|boolean'
'harus_dicek_fisik' => 'nullable|boolean',
```

**Reason:** Consistency with other nullable fields

## Testing Checklist

After applying these fixes, test the following scenarios:

### 1. Import with Empty Status/Kondisi
- [ ] Excel file with empty status cells
- [ ] Excel file with empty kondisi cells
- [ ] Verify default values are applied ('aktif', 'baik')

### 2. Import with Valid Status/Kondisi
- [ ] Excel file with valid status values
- [ ] Excel file with valid kondisi values
- [ ] Verify values are correctly saved

### 3. Import with Invalid Status/Kondisi
- [ ] Excel file with invalid status values
- [ ] Verify proper error messages in import logs
- [ ] Verify other valid rows are still imported

### 4. Transaction Handling
- [ ] Import file with mixed valid/invalid rows
- [ ] Verify valid rows are imported
- [ ] Verify invalid rows are logged with errors
- [ ] Verify no "transaction aborted" errors

### 5. Foreign Key Resolution
- [ ] Import with existing vendor/brand names
- [ ] Import with new vendor/brand names
- [ ] Verify auto-creation of master data

## Migration Steps

To apply these fixes:

1. **Run the new migration:**
   ```bash
   php artisan migrate
   ```

2. **Clear application cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

3. **Restart queue workers (if using queues):**
   ```bash
   php artisan queue:restart
   ```

4. **Test import functionality:**
   - Upload a test Excel file
   - Verify imports complete successfully
   - Check import logs for proper error handling

## Rollback Plan

If issues occur after deployment:

1. **Rollback migration:**
   ```bash
   php artisan migrate:rollback --step=1
   ```

2. **Revert code changes:**
   - Use git to revert the service files
   ```bash
   git checkout HEAD~1 app/Services/DataFilterService.php
   git checkout HEAD~1 app/Services/ExcelImportService.php
   git checkout HEAD~1 app/Models/FixedAsset.php
   ```

3. **Clear cache again:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

## Additional Recommendations

### 1. Add Validation at Upload
Consider adding pre-validation before import:
- Check required columns exist
- Validate data types
- Show preview with warnings

### 2. Improve Error Messages
Make error messages more user-friendly:
- Translate technical errors to Indonesian
- Provide suggestions for fixing data
- Show row numbers clearly

### 3. Add Import Templates
Provide downloadable Excel templates with:
- Column headers
- Example data
- Data validation rules
- Instructions sheet

### 4. Add Batch Processing
For large imports:
- Process in chunks (e.g., 100 rows at a time)
- Show progress bar
- Allow pause/resume
- Send email notification when complete

### 5. Add Data Preview
Before final import:
- Show first 10 rows
- Highlight potential issues
- Allow column mapping adjustment
- Confirm before processing

## Related Files

- `app/Services/ExcelImportService.php` - Main import logic
- `app/Services/DataFilterService.php` - Data validation and foreign key resolution
- `app/Services/ImportBatchService.php` - Batch management
- `app/Jobs/ProcessExcelImport.php` - Queue job handler
- `app/Models/FixedAsset.php` - Model definition
- `database/migrations/*_fixed_assets*.php` - Database schema

## Contact

For questions or issues related to these fixes, contact the development team.
