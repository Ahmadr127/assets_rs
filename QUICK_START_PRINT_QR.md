# 🚀 QUICK START - QR Code Print System

## Setup (Sekali Saja)

```bash
# 1. Migrate fresh & seed
php artisan migrate:fresh --seed

# 2. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Verify Installation

```bash
php artisan tinker
```

```php
// Check formats
\App\Models\PrintFormat::all();
// Should return 4 records

// Check default
\App\Models\PrintFormat::getDefault();
// Should return 6x5 format
```

## File Structure

```
app/
├── Helpers/
│   └── SvgHelper.php          ← SVG patching helper
├── Http/Controllers/
│   └── QRCodeController.php   ← Updated with SVG fix
└── Models/
    └── PrintFormat.php        ← Print format model

database/seeders/
└── PrintFormatSeeder.php      ← 4 formats (300 DPI)

resources/views/qr-codes/
└── printable-asset.blade.php  ← Template with JS fallback
```

## Print Formats

| Code | Size | QR Size | Use Case |
|------|------|---------|----------|
| 6x5 | 6×5 cm | 25mm | Asset besar (default) |
| 5x3 | 5×3 cm | 21mm | Asset sedang |
| 5x2.5 | 5×2.5 cm | 22mm | Asset kecil |
| 5x2 | 5×2 cm | 18mm | Asset mini |

## Usage

### User Flow:
1. Buka detail asset
2. Pilih ukuran dari dropdown
3. Klik "Print"
4. Print dialog muncul dengan ukuran yang benar
5. Print!

### API:
```
GET /qr/asset/{id}/print?format=6x5&autoprint=1
```

## Key Features

✅ **Accurate Sizing** - mm exact di layar & print
✅ **SVG Patching** - Force ukuran via helper
✅ **JS Fallback** - beforeprint enforcement
✅ **300 DPI** - Standard printer resolution
✅ **Scannable** - QR always readable

## Troubleshooting

### QR terlalu kecil/besar?
→ Check printer DPI (default: 300)
→ Update di SvgHelper.php & blade template

### SVG tidak ter-patch?
→ Clear cache: `php artisan cache:clear`
→ Check SvgHelper.php exists

### Tidak bisa scan?
→ Gunakan format lebih besar (6x5 atau 5x3)
→ Update error correction ke 'H'

## Testing Checklist

- [ ] Print preview shows correct mm size
- [ ] Physical print matches ruler measurement
- [ ] QR code scannable with smartphone
- [ ] All 4 formats work correctly
- [ ] Auto-print works (autoprint=1)

## Support

**DPI:** 300 (default), configurable
**Browsers:** Chrome, Firefox, Edge, Safari
**OS:** Windows, Mac, Linux

---

**Ready to use!** 🎉
Run `php artisan migrate:fresh --seed` and start printing!
