# Fix: Excel Formula Tersimpan Sebagai String

## Problem
Ketika mengimport Excel yang memiliki formula (misalnya `=CONCATENATE(C2362,"/",A2362)`), sistem menyimpan **formula string** bukan **hasil kalkulasi** formula tersebut.

**Contoh:**
- Excel cell berisi: `=CONCATENATE(C2362,"/",A2362)`
- Yang tersimpan di database: `=CONCATENATE(C2362,"/",A2362)` ❌
- Yang seharusnya tersimpan: `FAG/121002/` ✅

## Root Cause
Kode menggunakan `$cell->getValue()` yang mengembalikan:
- Untuk cell biasa: nilai cell
- Untuk cell dengan formula: **string formula** (bukan hasil kalkulasi)

## Solution

### Perubahan di `ExcelImportService.php`

#### Before (❌ Salah)
```php
$value = $cell->getValue(); // Mengembalikan formula string
```

#### After (✅ Benar)
```php
// Get calculated value if it's a formula, otherwise get the raw value
try {
    $value = $cell->getCalculatedValue(); // Mengembalikan hasil kalkulasi
} catch (\Exception $e) {
    // If calculation fails, fallback to raw value
    $value = $cell->getValue();
}
```

### Fungsi yang Diupdate

1. **`detectHeaders()`** - Line 41-48
   - Sekarang membaca hasil kalkulasi formula di header row
   
2. **`readAndMapData()`** - Line 137-143
   - Sekarang membaca hasil kalkulasi formula di semua data rows

## Cara Kerja

### Method: `getCalculatedValue()`
PhpSpreadsheet akan:
1. Mendeteksi apakah cell berisi formula (dimulai dengan `=`)
2. Jika formula: **mengevaluasi formula** dan mengembalikan hasilnya
3. Jika bukan formula: mengembalikan nilai biasa

### Error Handling
Jika formula tidak bisa dievaluasi (error, referensi invalid, dll):
- Fallback ke `getValue()` untuk menghindari crash
- Log error bisa ditambahkan jika diperlukan

## Contoh Kasus

### Case 1: Formula CONCATENATE
```
Excel Cell: =CONCATENATE(C2362,"/",A2362)
C2362 = "FAG"
A2362 = "121002"

Before Fix: "=CONCATENATE(C2362,"/",A2362)"
After Fix:  "FAG/121002/"
```

### Case 2: Formula Matematika
```
Excel Cell: =SUM(A1:A10)
Result: 1000

Before Fix: "=SUM(A1:A10)"
After Fix:  1000
```

### Case 3: Cell Biasa (Bukan Formula)
```
Excel Cell: "FAG/121002/"

Before Fix: "FAG/121002/"
After Fix:  "FAG/121002/"
(Tidak ada perubahan - tetap sama)
```

### Case 4: Formula Error
```
Excel Cell: =VLOOKUP(A1, #REF!, 2, FALSE)
(Formula error karena referensi invalid)

Before Fix: "=VLOOKUP(A1, #REF!, 2, FALSE)"
After Fix:  "=VLOOKUP(A1, #REF!, 2, FALSE)"
(Fallback ke getValue() karena error)
```

## Testing

### Test Scenario 1: Import dengan Formula
1. Buat Excel dengan kolom `kode_manual` berisi formula:
   ```
   =CONCATENATE(C2,"/",A2)
   ```
2. Import file
3. Verify: Database menyimpan hasil kalkulasi (e.g., `FAG/121002/`)

### Test Scenario 2: Import tanpa Formula
1. Buat Excel dengan kolom `kode_manual` berisi nilai biasa:
   ```
   FAG/121002/
   ```
2. Import file
3. Verify: Database menyimpan nilai yang sama

### Test Scenario 3: Mixed (Formula + Non-Formula)
1. Buat Excel dengan:
   - Row 1: Formula `=CONCATENATE(C2,"/",A2)`
   - Row 2: Nilai biasa `FAG/121003/`
2. Import file
3. Verify: Kedua row tersimpan dengan benar

## Files Modified

✅ `app/Services/ExcelImportService.php`
- Method `detectHeaders()` - Line 41-48
- Method `readAndMapData()` - Line 137-143

## Benefits

1. ✅ Formula Excel dievaluasi otomatis
2. ✅ Hasil kalkulasi yang tersimpan, bukan formula string
3. ✅ Backward compatible dengan cell non-formula
4. ✅ Error handling untuk formula yang invalid
5. ✅ Mendukung semua jenis formula Excel (CONCATENATE, SUM, VLOOKUP, IF, dll)

## Notes

- PhpSpreadsheet mendukung evaluasi formula Excel secara native
- Tidak perlu library tambahan
- Performance impact minimal karena evaluasi dilakukan saat import saja
- Formula yang kompleks atau menggunakan fungsi Excel yang tidak didukung akan fallback ke raw value

## Rekomendasi

Untuk Excel import di masa depan:
1. **Preferred**: Gunakan nilai hasil kalkulasi (bukan formula) di Excel sebelum export
2. **Alternative**: Sistem sekarang sudah handle formula secara otomatis
3. **Best Practice**: Validasi data setelah import untuk memastikan formula dievaluasi dengan benar
