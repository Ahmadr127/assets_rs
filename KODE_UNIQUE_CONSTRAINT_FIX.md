# Fix: Kode Unique Constraint Issue

## Problem
The system was throwing a unique constraint violation error when importing Excel data with duplicate `kode` values:
```
SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value violates unique constraint "fixed_assets_kode_unique"
DETAIL: Key (kode)=(FAG/121002/) already exists.
```

## Root Cause
- The `kode` column had a unique constraint (`fixed_assets_kode_unique`)
- Excel imports contained duplicate `kode` values (e.g., `FAG/121002/`)
- The system was trying to insert duplicate values, causing database constraint violations

## Solution Implemented

### 1. Database Schema Changes
**Migration:** `2025_11_08_001700_change_kode_to_kode_manual_unique.php`

- **Removed** unique constraint from `kode` column
- **Added** unique constraint to `kode_manual` column
- Now `kode` can have duplicate values, but `kode_manual` must be unique

### 2. Duplicate Detection Logic Update
**File:** `app/Services/DataFilterService.php`

Changed duplicate detection from checking `kode` to checking `kode_manual`:
- **Primary check:** `kode_manual` (unique constraint)
- **Secondary check:** `nama_fixed_asset + lokasi` combination

### 3. Model Validation Rules Update
**File:** `app/Models/FixedAsset.php`

- Removed unique validation from `kode`
- Added unique validation to `kode_manual`

### 4. Auto-generation Logic
**File:** `app/Services/ExcelImportService.php`

Added auto-generation for `kode_manual` if not provided:
- Format: `FAM20250108-0001` (FAM = Fixed Asset Manual)
- Ensures uniqueness even when not provided in Excel

## How It Works Now

### Duplicate Detection Strategy
The system now detects duplicates using two methods:

1. **By `kode_manual`** (if provided)
   - Checks if `kode_manual` already exists in database
   - This is the primary unique identifier

2. **By `nama_fixed_asset + lokasi`** (if `kode_manual` not checked)
   - Checks combination of asset name and location
   - Prevents duplicate assets in same location

### Import Behavior

#### Scenario 1: Excel has duplicate `kode` values
✅ **ALLOWED** - Multiple records can have the same `kode`
- Example: Multiple assets with `kode = "FAG/121002/"` can be imported

#### Scenario 2: Excel has duplicate `kode_manual` values
❌ **BLOCKED** - System will flag as duplicate
- Only one record with a specific `kode_manual` is allowed

#### Scenario 3: Excel has same `nama + lokasi`
❌ **BLOCKED** - System will flag as duplicate
- Prevents duplicate assets in the same location

### Auto-generation Rules

1. **If `kode` is empty:**
   - Auto-generates: `FA20250108-0001` (FA = Fixed Asset)

2. **If `kode_manual` is empty:**
   - Auto-generates: `FAM20250108-0001` (FAM = Fixed Asset Manual)

## Testing

### Test Case 1: Import with duplicate kode
```
Row 1: kode="FAG/121002/", kode_manual="M001", nama="Asset A"
Row 2: kode="FAG/121002/", kode_manual="M002", nama="Asset B"
```
✅ **Result:** Both imported successfully (different kode_manual)

### Test Case 2: Import with duplicate kode_manual
```
Row 1: kode="FAG/121002/", kode_manual="M001", nama="Asset A"
Row 2: kode="FAG/121003/", kode_manual="M001", nama="Asset B"
```
❌ **Result:** Row 2 flagged as duplicate (same kode_manual)

### Test Case 3: Import with duplicate nama+lokasi
```
Row 1: nama="Canopy", lokasi="Garasi", kode_manual="M001"
Row 2: nama="Canopy", lokasi="Garasi", kode_manual="M002"
```
❌ **Result:** Row 2 flagged as duplicate (same nama+lokasi)

## Migration Applied
```bash
php artisan migrate
```

✅ Migration `2025_11_08_001700_change_kode_to_kode_manual_unique` completed successfully

## Files Modified

1. ✅ `database/migrations/2025_11_08_001700_change_kode_to_kode_manual_unique.php` (NEW)
2. ✅ `app/Services/DataFilterService.php`
3. ✅ `app/Services/ExcelImportService.php`
4. ✅ `app/Models/FixedAsset.php`

## Summary

The system now:
- ✅ Allows duplicate `kode` values
- ✅ Enforces unique `kode_manual` values
- ✅ Detects duplicates by `kode_manual` OR `nama+lokasi`
- ✅ Auto-generates unique `kode_manual` when not provided
- ✅ Prevents database constraint violations during import

## Next Steps

1. Test the import with your Excel file
2. Verify that duplicate `kode` values are now accepted
3. Ensure `kode_manual` uniqueness is enforced
4. Check duplicate detection for `nama+lokasi` combinations
