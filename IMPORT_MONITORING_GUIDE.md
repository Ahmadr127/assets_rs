# 📊 Import Monitoring Guide

Panduan lengkap untuk monitoring proses import yang berjalan lama (2,396 rows).

---

## 🔴 1. Real-time Log Monitoring (Recommended)

### Windows PowerShell
```powershell
# Monitor log secara real-time (auto-update)
Get-Content -Path "storage\logs\laravel.log" -Wait -Tail 50
```

**Penjelasan:**
- `-Wait`: Terus monitor file, auto-update saat ada log baru
- `-Tail 50`: Tampilkan 50 baris terakhir
- **Stop dengan:** `Ctrl+C`

### Alternative: CMD
```cmd
powershell -Command "Get-Content -Path 'storage\logs\laravel.log' -Wait -Tail 50"
```

---

## 📈 2. Log Progress yang Ditambahkan

Setelah update code, kamu akan melihat log seperti ini:

### A. Start Import
```json
[2025-11-05 19:53:00] local.INFO: Starting import process
{
  "batch_id": 2,
  "total_rows": 2396,
  "action": "create"
}
```

### B. Progress Update (Setiap 100 Rows)
```json
[2025-11-05 19:53:15] local.INFO: Import progress
{
  "batch_id": 2,
  "processed": 100,
  "total": 2396,
  "progress": "4.17%",
  "success": 98,
  "failed": 2
}
```

```json
[2025-11-05 19:53:30] local.INFO: Import progress
{
  "batch_id": 2,
  "processed": 200,
  "total": 2396,
  "progress": "8.35%",
  "success": 195,
  "failed": 5
}
```

### C. Completion
```json
[2025-11-05 19:55:00] local.INFO: Import process completed
{
  "batch_id": 2,
  "total_processed": 2396,
  "success": 2350,
  "failed": 46,
  "updated": 0
}
```

---

## 🔍 3. Check Queue Worker Status

### Lihat PHP Process yang Running
```powershell
Get-Process -Name php | Select-Object Id, ProcessName, StartTime, CPU
```

**Output Example:**
```
Id    ProcessName StartTime           CPU
--    ----------- ---------           ---
9724  php         11/5/2025 7:40:39   34.125  ← Queue Worker (high CPU = sedang proses)
1376  php         11/5/2025 7:39:58   2.09375 ← Web Server
```

### Monitor Queue Worker
Jika queue worker belum jalan, start dengan:
```bash
php artisan queue:work --tries=3 --timeout=300
```

---

## 📊 4. Check Database Progress

### Query Import Batch Status
```sql
SELECT 
    id,
    filename,
    total_rows,
    processed_rows,
    success_rows,
    failed_rows,
    status,
    created_at,
    completed_at
FROM import_batches
WHERE id = 2;
```

### Query Import Logs Count
```sql
-- Total logs processed
SELECT 
    status,
    COUNT(*) as count
FROM import_logs
WHERE import_batch_id = 2
GROUP BY status;
```

**Expected Output:**
```
status     | count
-----------|------
imported   | 2350
error      | 46
duplicate  | 0
```

---

## 🚀 5. Performance Monitoring

### A. Check Memory Usage
```powershell
Get-Process -Name php | Select-Object Id, ProcessName, WS
```

**WS** = Working Set (Memory in bytes)

### B. Check Disk I/O
```powershell
# Monitor log file size
Get-Item "storage\logs\laravel.log" | Select-Object Name, Length, LastWriteTime
```

### C. Check Queue Jobs
```bash
# List pending jobs
php artisan queue:monitor

# Or check database
SELECT * FROM jobs WHERE queue = 'default' LIMIT 10;
```

---

## 🐛 6. Troubleshooting Slow Import

### A. Jika Progress Stuck di "VALIDATING"

**Kemungkinan Penyebab:**
1. Queue worker tidak jalan
2. Job failed dan tidak retry
3. Memory limit tercapai

**Solusi:**
```bash
# 1. Restart queue worker
php artisan queue:restart

# 2. Check failed jobs
php artisan queue:failed

# 3. Retry failed jobs
php artisan queue:retry all

# 4. Increase memory limit (config/queue.php)
'memory' => 512, // MB
```

### B. Jika Import Sangat Lambat

**Optimasi:**

1. **Disable Query Log** (sementara)
```php
// In AppServiceProvider.php boot()
DB::connection()->disableQueryLog();
```

2. **Batch Insert** (future improvement)
```php
// Instead of: FixedAsset::create($data)
// Use: FixedAsset::insert($batchData) // 100 rows at once
```

3. **Increase PHP Limits**
```ini
; php.ini
max_execution_time = 600
memory_limit = 512M
```

4. **Database Connection Pool**
```env
DB_CONNECTION_POOL_SIZE=10
```

---

## 📝 7. Log File Management

### A. Clear Old Logs
```bash
# Clear log file
echo "" > storage/logs/laravel.log

# Or rotate logs
php artisan log:clear
```

### B. Filter Logs by Batch ID
```powershell
# Windows PowerShell
Select-String -Path "storage\logs\laravel.log" -Pattern "batch_id.*2" | Select-Object -Last 20
```

### C. Export Logs
```powershell
# Export import logs to file
Get-Content "storage\logs\laravel.log" | 
    Select-String -Pattern "Import progress" | 
    Out-File "import_progress_batch2.log"
```

---

## 🎯 8. Real-time Progress in Browser

### Option 1: Polling (Current)
Browser auto-refresh setiap 5 detik untuk update progress.

### Option 2: WebSocket (Future Enhancement)
```javascript
// resources/js/import-monitor.js
Echo.channel('import.' + batchId)
    .listen('ImportProgress', (e) => {
        console.log('Progress:', e.progress);
        updateProgressBar(e.progress);
    });
```

---

## 📋 9. Monitoring Checklist

Saat import berjalan, monitor hal berikut:

- [ ] **Queue Worker Running**
  ```bash
  ps aux | grep "queue:work"  # Linux
  Get-Process -Name php       # Windows
  ```

- [ ] **Log File Growing**
  ```bash
  watch -n 1 ls -lh storage/logs/laravel.log  # Linux
  # Windows: manual refresh
  ```

- [ ] **Database Rows Increasing**
  ```sql
  SELECT COUNT(*) FROM import_logs WHERE import_batch_id = 2;
  ```

- [ ] **Memory Not Exhausted**
  ```bash
  free -m  # Linux
  Get-Process php | Select WS  # Windows
  ```

- [ ] **No PHP Errors**
  ```bash
  tail -f storage/logs/laravel.log | grep ERROR
  ```

---

## 🔧 10. Quick Commands Reference

### Start Monitoring
```powershell
# Terminal 1: Queue Worker
php artisan queue:work --tries=3 --timeout=300 --verbose

# Terminal 2: Log Monitor
Get-Content -Path "storage\logs\laravel.log" -Wait -Tail 50

# Terminal 3: Process Monitor
while ($true) { 
    Get-Process -Name php | Select Id, CPU, WS; 
    Start-Sleep -Seconds 5; 
    Clear-Host 
}
```

### Stop Everything
```powershell
# Stop queue worker
Ctrl+C

# Kill all PHP processes (CAREFUL!)
Get-Process -Name php | Stop-Process -Force
```

---

## 📊 Expected Timeline for 2,396 Rows

**Estimasi berdasarkan performance:**

| Rows | Time | Speed |
|------|------|-------|
| 100  | ~15s | 6.7 rows/sec |
| 500  | ~75s | 6.7 rows/sec |
| 1000 | ~150s (2.5 min) | 6.7 rows/sec |
| 2396 | **~360s (6 min)** | 6.7 rows/sec |

**Faktor yang mempengaruhi:**
- Database performance
- Foreign key lookups
- Validation complexity
- Server resources
- Network latency (if remote DB)

---

## ✅ Success Indicators

Import berhasil jika:

1. ✅ Status berubah dari "VALIDATING" → "Processing..." → "Completed"
2. ✅ Progress bar mencapai 100%
3. ✅ Success count mendekati total rows
4. ✅ Log menampilkan "Import process completed"
5. ✅ Batch status = "completed" di database
6. ✅ No critical errors in log

---

## 🚨 Error Indicators

Import bermasalah jika:

1. ❌ Stuck di "VALIDATING" > 5 menit
2. ❌ Failed count > 50% dari total
3. ❌ Queue worker crash (PHP process hilang)
4. ❌ Memory exhausted errors
5. ❌ Database connection errors
6. ❌ Timeout errors

**Action:** Check logs, restart queue worker, retry failed jobs.

---

## 📞 Support

Jika masih ada masalah:

1. **Export logs:**
   ```bash
   Get-Content storage\logs\laravel.log | Out-File debug.log
   ```

2. **Check failed jobs:**
   ```bash
   php artisan queue:failed
   ```

3. **Database status:**
   ```sql
   SELECT * FROM import_batches WHERE id = 2;
   SELECT status, COUNT(*) FROM import_logs WHERE import_batch_id = 2 GROUP BY status;
   ```

4. **System info:**
   ```bash
   php -v
   php -i | grep memory_limit
   php -i | grep max_execution_time
   ```

---

## 🎓 Tips & Best Practices

1. **Always run queue worker** sebelum import
2. **Monitor logs** saat import berjalan
3. **Don't close browser** saat import (jika perlu progress update)
4. **Backup database** sebelum import besar
5. **Test dengan sample data** dulu (100 rows)
6. **Clear old logs** untuk performance
7. **Set realistic timeout** (5-10 menit untuk 2000+ rows)

---

**Last Updated:** 2025-11-05  
**Version:** 1.0
