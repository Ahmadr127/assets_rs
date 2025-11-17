# 🚀 cPanel Deployment Guide - Import Feature

Panduan deploy aplikasi Laravel dengan fitur import ke cPanel/shared hosting.

---

## ❌ Masalah di cPanel

### Gejala
- Import stuck di "VALIDATING" forever
- Progress bar tidak bergerak
- Log tidak muncul
- Data tidak masuk database

### Penyebab
**Queue worker tidak berjalan** karena:
1. ❌ Tidak ada akses SSH persistent
2. ❌ Tidak bisa run `php artisan queue:work`
3. ❌ Background process tidak didukung
4. ❌ Cron job terbatas

---

## ✅ Solusi: Gunakan Sync Queue Driver

### 1. Update `.env` di cPanel

```env
# Change from:
QUEUE_CONNECTION=database

# To:
QUEUE_CONNECTION=sync
```

**Penjelasan:**
- `sync` = Process langsung tanpa queue
- `database` = Butuh queue worker (tidak bisa di cPanel)

### 2. Clear Config Cache

Setelah update `.env`, jalankan:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 3. Test Import

Import sekarang akan:
- ✅ Process langsung saat submit
- ✅ Tidak butuh queue worker
- ✅ Redirect langsung ke hasil
- ✅ Log muncul di `storage/logs/laravel.log`

---

## 🔧 Konfigurasi PHP untuk Large Import

### A. Update `.htaccess` di `public/`

```apache
<IfModule mod_php.c>
    php_value max_execution_time 600
    php_value memory_limit 512M
    php_value upload_max_filesize 50M
    php_value post_max_size 50M
</IfModule>
```

### B. Atau Update `php.ini` (jika ada akses)

```ini
max_execution_time = 600
memory_limit = 512M
upload_max_filesize = 50M
post_max_size = 50M
max_input_time = 600
```

### C. Via cPanel PHP Selector

1. Login cPanel
2. Pilih **Select PHP Version**
3. Klik **Switch To PHP Options**
4. Update:
   - `max_execution_time` → 600
   - `memory_limit` → 512M
   - `upload_max_filesize` → 50M
   - `post_max_size` → 50M

---

## 📊 Monitoring di cPanel

### 1. Check Log File

Via **File Manager** atau **SSH**:

```bash
# View last 50 lines
tail -n 50 storage/logs/laravel.log

# Monitor real-time (if SSH available)
tail -f storage/logs/laravel.log

# Search for errors
grep "ERROR" storage/logs/laravel.log
```

### 2. Check Database

Via **phpPgAdmin** atau **psql**:

```sql
-- Check import batch status
SELECT id, filename, status, total_rows, success_rows, failed_rows 
FROM import_batches 
ORDER BY created_at DESC 
LIMIT 10;

-- Check import logs
SELECT status, COUNT(*) 
FROM import_logs 
WHERE import_batch_id = 2 
GROUP BY status;

-- Check queue jobs (should be empty with sync driver)
SELECT * FROM jobs LIMIT 10;
```

### 3. Check PHP Errors

Via cPanel **Error Log**:
- Home → Metrics → Errors
- Look for PHP Fatal errors, timeouts, memory exhausted

---

## 🐛 Troubleshooting

### Problem 1: Import Timeout

**Error:**
```
Maximum execution time of 30 seconds exceeded
```

**Solution:**
```php
// Already handled in code:
set_time_limit(600); // 10 minutes
```

Or update PHP settings (see above).

---

### Problem 2: Memory Exhausted

**Error:**
```
Allowed memory size of 134217728 bytes exhausted
```

**Solution:**
```php
// Already handled in code:
ini_set('memory_limit', '512M');
```

Or update PHP settings (see above).

---

### Problem 3: Upload File Too Large

**Error:**
```
The file exceeds your upload_max_filesize ini directive
```

**Solution:**
Update `.htaccess` or `php.ini`:
```ini
upload_max_filesize = 50M
post_max_size = 50M
```

---

### Problem 4: PostgreSQL Connection Error

**Error:**
```
SQLSTATE[08006] could not connect to server
```

**Solution:**
1. Check `.env` database credentials
2. Verify PostgreSQL is running
3. Check database host (might be `localhost` or IP)
4. Verify PostgreSQL port (default: 5432)

```env
DB_CONNECTION=pgsql
DB_HOST=localhost  # or 127.0.0.1 or specific IP
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

---

### Problem 5: Permission Denied

**Error:**
```
file_put_contents(storage/logs/laravel.log): failed to open stream
```

**Solution:**
```bash
# Set correct permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Or via File Manager:
# Right-click → Change Permissions → 775
```

---

## 🚀 Deployment Checklist

### Pre-Deployment

- [ ] Update `.env` with `QUEUE_CONNECTION=sync`
- [ ] Test import locally with sync driver
- [ ] Backup production database
- [ ] Check PHP version compatibility (>= 8.1)
- [ ] Verify PostgreSQL version (>= 12)

### Deployment Steps

1. **Upload Files**
   ```bash
   # Via FTP/SFTP or Git
   git pull origin main
   ```

2. **Install Dependencies**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

4. **Clear Caches**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

5. **Set Permissions**
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

6. **Test Import**
   - Upload small Excel file (10-20 rows)
   - Verify import completes
   - Check logs for errors

### Post-Deployment

- [ ] Test import with sample data
- [ ] Verify logs are being written
- [ ] Check database records
- [ ] Monitor PHP error logs
- [ ] Test with larger file (100+ rows)

---

## 📈 Performance Optimization

### 1. Database Indexing

Ensure indexes exist on frequently queried columns:

```sql
-- Check existing indexes
SELECT indexname, indexdef 
FROM pg_indexes 
WHERE tablename = 'fixed_assets';

-- Add indexes if missing
CREATE INDEX idx_fixed_assets_kode ON fixed_assets(kode);
CREATE INDEX idx_fixed_assets_status ON fixed_assets(status);
CREATE INDEX idx_import_logs_batch ON import_logs(import_batch_id);
```

### 2. Disable Debug Mode

```env
APP_DEBUG=false
APP_ENV=production
```

### 3. Enable OPcache

Via cPanel PHP Selector:
- Enable `opcache`
- Set `opcache.enable=1`

### 4. Optimize Composer Autoloader

```bash
composer dump-autoload --optimize --no-dev
```

---

## 🔄 Alternative: Cron Job for Queue (Advanced)

If you have cron job access, you can use database queue:

### 1. Keep `.env` as:
```env
QUEUE_CONNECTION=database
```

### 2. Setup Cron Job

Via cPanel → Cron Jobs:

```bash
# Run every minute
* * * * * cd /home/username/public_html && php artisan queue:work --stop-when-empty --max-time=60 >> /dev/null 2>&1
```

**Note:** This runs queue worker every minute for 60 seconds, then stops.

### 3. Pros & Cons

**Pros:**
- ✅ Non-blocking for user
- ✅ Better for multiple concurrent imports
- ✅ Can retry failed jobs

**Cons:**
- ❌ Delay (up to 1 minute)
- ❌ Cron job might not run reliably
- ❌ More complex to debug

**Recommendation:** Use `sync` driver for simplicity on cPanel.

---

## 📝 Environment Comparison

| Feature | Local (database queue) | cPanel (sync) |
|---------|----------------------|---------------|
| Queue Worker | ✅ Manual run | ❌ Not available |
| Processing | ⚡ Background | 🔄 Immediate |
| User Wait | ❌ No (async) | ✅ Yes (sync) |
| Timeout Risk | ❌ Low | ⚠️ Medium |
| Setup | 🔧 Complex | ✅ Simple |
| Debugging | 📊 Separate logs | 📝 Direct logs |
| Recommended For | VPS/Dedicated | Shared Hosting |

---

## 🎯 Expected Behavior

### With `QUEUE_CONNECTION=sync`

1. User uploads Excel file
2. User configures mapping
3. User clicks "Process Import"
4. **Browser waits** (loading spinner)
5. Import processes immediately
6. Redirect to results page
7. Success/error message shown

**Timeline for 2,396 rows:** ~6 minutes

### With `QUEUE_CONNECTION=database` (requires worker)

1. User uploads Excel file
2. User configures mapping
3. User clicks "Process Import"
4. Redirect to progress page immediately
5. **Background job processes** (if worker running)
6. Progress updates via polling
7. Completion notification

---

## ✅ Verification Steps

After deployment, verify:

1. **Config is correct:**
   ```bash
   php artisan config:show queue.default
   # Should output: sync
   ```

2. **Import works:**
   - Upload test Excel file
   - Complete mapping
   - Click "Process Import"
   - Wait for completion
   - Verify data in database

3. **Logs are written:**
   ```bash
   tail -n 20 storage/logs/laravel.log
   # Should see import logs
   ```

4. **No queue jobs stuck:**
   ```sql
   SELECT COUNT(*) FROM jobs;
   -- Should be 0 with sync driver
   ```

---

## 📞 Support Checklist

If import still not working, collect:

1. **Environment info:**
   ```bash
   php -v
   php -m | grep -E "pgsql|zip|xml"
   ```

2. **Laravel config:**
   ```bash
   php artisan about
   ```

3. **Error logs:**
   ```bash
   tail -n 100 storage/logs/laravel.log > debug.log
   ```

4. **Database status:**
   ```sql
   SELECT * FROM import_batches ORDER BY created_at DESC LIMIT 5;
   ```

5. **PHP settings:**
   ```bash
   php -i | grep -E "max_execution_time|memory_limit|upload_max_filesize"
   ```

---

## 🎓 Summary

### Key Changes for cPanel

1. ✅ **Queue Driver:** `sync` instead of `database`
2. ✅ **PHP Limits:** Increased timeout & memory
3. ✅ **Logging:** Enhanced for debugging
4. ✅ **Error Handling:** Better error messages
5. ✅ **Permissions:** Correct storage permissions

### Why It Works Now

- **No queue worker needed** → Works on shared hosting
- **Immediate processing** → No stuck jobs
- **Better logging** → Easier debugging
- **Increased limits** → Handles large files
- **Fallback logic** → Auto-detects environment

---

**Last Updated:** 2025-11-05  
**Tested On:** cPanel with PostgreSQL  
**Laravel Version:** 11.x  
**PHP Version:** 8.1+
