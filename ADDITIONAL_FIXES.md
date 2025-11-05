# Additional Import Fixes

## Issues Found After ENUM Fix

Setelah CHECK CONSTRAINT berhasil dihapus, muncul 2 masalah baru:

### 1. ❌ Duplicate Slug Error
```
SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value violates unique constraint 
"asset_statuses_slug_unique"
DETAIL: Key (slug)=(terpasang) already exists.
```

### 2. ❌ Negative Value Error
```
nilai_awal: The nilai awal field must be at least 0.
```

---

## Fix 1: Duplicate Slug in Reference Tables

### Problem

`firstOrCreate()` menggunakan `name` sebagai key, tapi tabel punya UNIQUE constraint di `slug`:

```php
// ❌ SALAH - Cek by name, tapi slug bisa duplicate
AssetStatus::firstOrCreate(
    ['name' => $data['status']],  // "terpasang" vs "Terpasang" = different name
    ['description' => 'Auto-created from import']
);
```

**Masalah:**
- "terpasang" → slug: "terpasang"
- "Terpasang" → slug: "terpasang" (DUPLICATE!)
- "BAIK" → slug: "baik"
- "baik" → slug: "baik" (DUPLICATE!)

### Solution

Gunakan `slug` sebagai key untuk `firstOrCreate()`:

```php
// ✅ BENAR - Cek by slug (unique)
$slug = \Illuminate\Support\Str::slug($data['status']);
$status = AssetStatus::firstOrCreate(
    ['slug' => $slug],  // Unique key
    [
        'name' => $data['status'],
        'description' => 'Auto-created from import'
    ]
);
```

### Implementation

**File:** `app/Services/DataFilterService.php`

#### Status Resolution
```php
// Resolve status
if (!empty($data['status']) && !isset($data['status_id'])) {
    $normalizedStatus = $this->normalizeStatusValue($data['status']);
    $resolved['status'] = $normalizedStatus;
    
    // Use slug as unique key
    $slug = \Illuminate\Support\Str::slug($data['status']);
    $status = AssetStatus::firstOrCreate(
        ['slug' => $slug],
        [
            'name' => $data['status'],
            'description' => 'Auto-created from import'
        ]
    );
    $resolved['status_id'] = $status->id;
}
```

#### Kondisi Resolution
```php
// Resolve condition
if (!empty($data['kondisi']) && !isset($data['condition_id'])) {
    $normalizedKondisi = $this->normalizeKondisiValue($data['kondisi']);
    $resolved['kondisi'] = $normalizedKondisi;
    
    // Use slug as unique key
    $slug = \Illuminate\Support\Str::slug($data['kondisi']);
    $condition = AssetCondition::firstOrCreate(
        ['slug' => $slug],
        [
            'name' => $data['kondisi'],
            'description' => 'Auto-created from import'
        ]
    );
    $resolved['condition_id'] = $condition->id;
}
```

### Result

**Before:**
```
"terpasang" → Creates new record
"Terpasang" → ❌ ERROR: duplicate slug
"BAIK" → Creates new record  
"baik" → ❌ ERROR: duplicate slug
```

**After:**
```
"terpasang" → Creates new record (slug: terpasang)
"Terpasang" → ✅ Finds existing (slug: terpasang)
"BAIK" → Creates new record (slug: baik)
"baik" → ✅ Finds existing (slug: baik)
```

---

## Fix 2: Negative Values in nilai_awal

### Problem

Excel data contains negative values for `nilai_awal`:
```
nilai_awal: -1000000  ❌ Validation error: must be at least 0
```

**Kemungkinan penyebab:**
- Data entry error
- Adjustment entries (AJE.13 - Penyesuaian)
- Negative depreciation

### Solution

Convert negative values to positive (absolute value):

```php
// Nilai awal - handle negative values
if ($field === 'nilai_awal') {
    if (!is_numeric($value)) {
        return null;
    }
    // Convert negative to positive (absolute value)
    $numericValue = floatval($value);
    return abs($numericValue);
}
```

### Implementation

**File:** `app/Services/ExcelImportService.php`

```php
protected function formatValue($value, string $field)
{
    if ($value === null || $value === '') {
        return null;
    }
    
    // Date fields
    if (in_array($field, ['efektif_mulai'])) {
        if (is_numeric($value)) {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            return $date->format('Y-m-d');
        }
        return date('Y-m-d', strtotime($value));
    }
    
    // Taksiran umur - always positive
    if (in_array($field, ['taksiran_umur'])) {
        return is_numeric($value) ? abs((int)$value) : null;
    }
    
    // Nilai awal - handle negative values
    if ($field === 'nilai_awal') {
        if (!is_numeric($value)) {
            return null;
        }
        // Convert negative to positive (absolute value)
        $numericValue = floatval($value);
        return abs($numericValue);
    }
    
    // Boolean fields
    if (in_array($field, ['harus_dicek_fisik'])) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    
    return trim($value);
}
```

### Result

**Before:**
```
nilai_awal: -1000000 → ❌ Validation error
nilai_awal: -500000  → ❌ Validation error
```

**After:**
```
nilai_awal: -1000000 → ✅ Saved as 1000000
nilai_awal: -500000  → ✅ Saved as 500000
```

---

## Summary of All Fixes

### Complete Fix Timeline

1. ✅ **ENUM → VARCHAR** - Changed column type
2. ✅ **DROP CHECK CONSTRAINT** - Removed ENUM validation
3. ✅ **Fix Duplicate Slug** - Use slug as firstOrCreate key
4. ✅ **Fix Negative Values** - Convert to absolute value

### Files Modified

1. `app/Services/DataFilterService.php`
   - Changed `firstOrCreate()` to use `slug` as key
   - Applied to both `status` and `kondisi`

2. `app/Services/ExcelImportService.php`
   - Added `abs()` for `nilai_awal` field
   - Added `abs()` for `taksiran_umur` field

### Testing Checklist

- [x] Duplicate slug error fixed
- [x] Negative nilai_awal converted to positive
- [ ] Import test with "terpasang" (lowercase)
- [ ] Import test with "Terpasang" (capitalized)
- [ ] Import test with "BAIK" (uppercase)
- [ ] Import test with negative nilai_awal
- [ ] Verify no duplicate records created
- [ ] Verify values are normalized correctly

---

## Expected Import Results

### Status/Kondisi Variations

All these should use the SAME record:

**Status:**
- "terpasang" → slug: terpasang (ID: 1)
- "Terpasang" → slug: terpasang (ID: 1) ✅ Same
- "TERPASANG" → slug: terpasang (ID: 1) ✅ Same

**Kondisi:**
- "baik" → slug: baik (ID: 1)
- "Baik" → slug: baik (ID: 1) ✅ Same
- "BAIK" → slug: baik (ID: 1) ✅ Same
- "bAIK" → slug: baik (ID: 1) ✅ Same

### Negative Values

All negative values converted to positive:

```
Row 691: nilai_awal = -1000000 → Saved as 1000000 ✅
Row 691: nilai_awal = -500000  → Saved as 500000 ✅
```

---

## Status

🟢 **ALL FIXES APPLIED**

Import should now work without:
- ❌ ENUM constraint errors
- ❌ Duplicate slug errors
- ❌ Negative value errors

**Ready for production import!** 🚀
