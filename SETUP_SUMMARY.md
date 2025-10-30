# Setup Summary - QR Code & Menu Permissions

## ✅ Perubahan yang Telah Selesai

### 1. **Public Route untuk QR Code Scanning**
**File**: `routes/web.php`

```php
// Public route - bisa diakses tanpa login
Route::get('/fixed-assets/{fixedAsset}', [FixedAssetController::class, 'show'])
    ->name('fixed-assets.show');
```

**URL**: `http://127.0.0.1:8000/fixed-assets/1`
- ✅ Bisa diakses tanpa login
- ✅ Perfect untuk QR code scanning
- ✅ Menampilkan detail asset

---

### 2. **QR Code Download - Fixed (SVG Format)**
**File**: `app/Http/Controllers/QRCodeController.php`

**Masalah**: Imagick extension tidak terinstall
**Solusi**: Menggunakan SVG format (tidak perlu imagick)

**Keuntungan SVG**:
- ✅ Tidak perlu install imagick
- ✅ Kualitas vector (bisa di-zoom tanpa blur)
- ✅ Ukuran file lebih kecil
- ✅ Bisa dibuka di semua browser modern

**URL Download**: `http://127.0.0.1:8000/qr/asset/1/download?size=400&format=svg`

---

### 3. **Print QR Code - Optimized untuk 1 Halaman**
**File**: `resources/views/qr-codes/printable-asset.blade.php`

**Optimasi**:
- ✅ QR code size: 200px (dari 300px)
- ✅ Spacing dikurangi
- ✅ Font size lebih kecil
- ✅ CSS `@page { size: A4; margin: 10mm; }`
- ✅ URL section disembunyikan saat print (class `no-print`)

**URL Print**: `http://127.0.0.1:8000/qr/asset/1/print?autoprint=1`

---

### 4. **Menu Permission System**

#### Migration Baru
**File**: `database/migrations/2025_10_30_075027_add_group_to_permissions_table.php`

Menambahkan kolom `group` ke tabel `permissions`:
```php
$table->string('group')->nullable()->comment('Group/category of permission');
```

#### Seeder Baru
**File**: `database/seeders/MenuPermissionSeeder.php`

**Permissions yang Dibuat**:

| Permission Name | Display Name | Description |
|----------------|--------------|-------------|
| `menu_dashboard` | Menu Dashboard | Akses menu Dashboard |
| `menu_fixed_assets` | Menu Fixed Assets | Akses menu Fixed Assets |
| `menu_master_data` | Menu Master Data | Akses menu Master Data |
| `menu_master_locations` | Menu Lokasi | Akses submenu Lokasi |
| `menu_master_statuses` | Menu Status | Akses submenu Status |
| `menu_master_conditions` | Menu Kondisi | Akses submenu Kondisi |
| `menu_master_vendors` | Menu Vendor | Akses submenu Vendor |
| `menu_master_brands` | Menu Brand | Akses submenu Brand |
| `menu_master_types` | Menu Tipe Asset | Akses submenu Tipe Asset |
| `menu_user_management` | Menu User Management | Akses menu User Management |
| `menu_users` | Menu Users | Akses submenu Users |
| `menu_roles` | Menu Roles | Akses submenu Roles |
| `menu_permissions` | Menu Permissions | Akses submenu Permissions |

#### Role Assignment

**Admin:**
- ✅ Semua menu permissions

**Librarian:**
- ✅ Dashboard
- ✅ Fixed Assets
- ✅ Master Data (semua submenu)

**User:**
- ✅ Dashboard saja

---

## 🚀 Cara Menggunakan

### 1. Jalankan Migration & Seeder
```bash
php artisan migrate:fresh --seed
```

### 2. Login dengan User Admin
- Username: `admin`
- Email: `admin@example.com`
- Password: (sesuai factory default)

### 3. Test QR Code Features

#### Download QR Code
1. Buka: `http://127.0.0.1:8000/fixed-assets/1`
2. Klik tombol "Download" (hijau)
3. File SVG akan terdownload

#### Print QR Code
1. Buka: `http://127.0.0.1:8000/fixed-assets/1`
2. Klik tombol "Print" (biru)
3. Halaman print akan terbuka (1 halaman saja)

#### Scan QR Code (Public Access)
1. Generate QR code dari halaman asset
2. Scan dengan HP
3. Akan membuka: `http://127.0.0.1:8000/fixed-assets/1`
4. **Tidak perlu login!**

---

## 📝 Cara Implementasi Menu Permission di Sidebar

### Contoh Penggunaan di Blade

**File**: `resources/views/layouts/app.blade.php`

```blade
{{-- Dashboard Menu --}}
@if(auth()->check() && auth()->user()->hasPermission('menu_dashboard'))
<li>
    <a href="{{ route('dashboard') }}" class="...">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
</li>
@endif

{{-- Fixed Assets Menu --}}
@if(auth()->check() && auth()->user()->hasPermission('menu_fixed_assets'))
<li>
    <a href="{{ route('fixed-assets.index') }}" class="...">
        <i class="fas fa-building"></i>
        <span>Fixed Assets</span>
    </a>
</li>
@endif

{{-- Master Data Menu --}}
@if(auth()->check() && auth()->user()->hasPermission('menu_master_data'))
<li>
    <div class="menu-header">Master Data</div>
    <ul>
        @if(auth()->user()->hasPermission('menu_master_locations'))
        <li>
            <a href="{{ route('masters.locations.index') }}">Lokasi</a>
        </li>
        @endif
        
        @if(auth()->user()->hasPermission('menu_master_statuses'))
        <li>
            <a href="{{ route('masters.statuses.index') }}">Status</a>
        </li>
        @endif
        
        {{-- ... dan seterusnya --}}
    </ul>
</li>
@endif
```

---

## 🔧 Troubleshooting

### QR Code Download Error: "imagick extension required"
**Solusi**: Sudah diperbaiki, sekarang menggunakan SVG format

### Print QR Code Terpotong 3 Halaman
**Solusi**: Sudah dioptimasi, sekarang muat dalam 1 halaman

### Menu Permission Error saat Seeding
**Solusi**: Pastikan migration `add_group_to_permissions_table` sudah dijalankan

### Public Route Tidak Bisa Diakses
**Solusi**: 
```bash
php artisan route:clear
php artisan route:cache
```

---

## 📊 Database Schema

### Tabel: `permissions`
```sql
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `description` text,
  `group` varchar(255) DEFAULT NULL COMMENT 'Group/category of permission',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
);
```

### Tabel: `role_permission`
```sql
CREATE TABLE `role_permission` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_permission_role_id_foreign` (`role_id`),
  KEY `role_permission_permission_id_foreign` (`permission_id`),
  CONSTRAINT `role_permission_role_id_foreign` 
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permission_permission_id_foreign` 
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
);
```

---

## ✨ Fitur Tambahan yang Bisa Dikembangkan

1. **Permission Management UI**
   - CRUD untuk menu permissions
   - Assign permissions ke role via UI

2. **Dynamic Menu Builder**
   - Menu dibangun otomatis dari permissions
   - Tidak perlu hardcode di blade

3. **Permission Groups**
   - Group permissions by module
   - Easier management

4. **Audit Log**
   - Track siapa mengakses menu apa
   - Security monitoring

---

## 📞 Support

Jika ada pertanyaan atau masalah, silakan cek:
1. File `QR_DOWNLOAD_FIX.md` untuk detail QR code fixes
2. File `MENU_PERMISSION_SETUP.md` untuk detail menu permissions
3. Laravel logs di `storage/logs/laravel.log`

---

**Last Updated**: 2025-10-30
**Version**: 1.0.0
