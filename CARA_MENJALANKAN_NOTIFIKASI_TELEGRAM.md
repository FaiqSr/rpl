# 🚀 Cara Menjalankan Notifikasi Telegram Otomatis

## ⚠️ PENTING: Scheduler Harus Berjalan!

Notifikasi Telegram **TIDAK AKAN** berfungsi jika Laravel Scheduler tidak berjalan. Scheduler harus berjalan di **background/terminal terpisah**.

## 📋 Checklist Sebelum Menjalankan

1. ✅ **Toggle Aktif** - Di halaman "Manajemen Informasi", toggle harus **ON** (hijau)
2. ✅ **Token Bot & Chat ID** - Sudah diisi dan di-test (tombol "Tes Kirim Pesan" berhasil)
3. ✅ **Kondisi Kandang** - Harus **PERHATIAN** atau **BURUK** (bukan BAIK)
   - Notifikasi hanya dikirim saat kondisi tidak baik
   - Jika kondisi BAIK, notifikasi tidak akan dikirim

## 🚀 Langkah Menjalankan Scheduler

### Windows (Laragon):

**Cara 1: Double-click file**
```
START_TELEGRAM_NOTIFICATIONS.bat
```

**Cara 2: Manual via Command Prompt**
```bash
cd C:\laragon\www\GitHub\rpl
php artisan schedule:work
```

**Cara 3: PowerShell**
```powershell
cd C:\laragon\www\GitHub\rpl
php artisan schedule:work
```

### Verifikasi Scheduler Berjalan

Setelah menjalankan `php artisan schedule:work`, Anda akan melihat output seperti:
```
Running scheduled tasks every minute.
```

**PENTING:** 
- Scheduler akan terus berjalan di terminal tersebut
- **JANGAN TUTUP TERMINAL** jika ingin notifikasi tetap berjalan
- Jika terminal ditutup, scheduler berhenti dan notifikasi tidak akan dikirim

## ✅ Test Notifikasi Manual

Buka terminal **BARU** (jangan tutup yang menjalankan scheduler) dan jalankan:
```bash
cd C:\laragon\www\GitHub\rpl
php artisan telegram:send-monitoring
```

Jika berhasil, Anda akan melihat:
```
✅ Telegram notification sent successfully at 2025-11-25 XX:XX:XX (Status: PERHATIAN)
```

Dan pesan akan muncul di Telegram Anda.

## 🔍 Troubleshooting

### Notifikasi tidak terkirim:

1. **Cek Scheduler Berjalan:**
   - Pastikan terminal dengan `php artisan schedule:work` masih terbuka
   - Terminal harus menunjukkan "Running scheduled tasks every minute"
   - Jika terminal ditutup, scheduler berhenti dan notifikasi tidak akan dikirim

2. **Cek Toggle Aktif:**
   - Buka "Manajemen Informasi"
   - Pastikan toggle **ON** (hijau)
   - Status harus "Aktif"

3. **Cek Kondisi Kandang:**
   - Notifikasi hanya dikirim saat kondisi **PERHATIAN** atau **BURUK**
   - Jika kondisi **BAIK**, notifikasi tidak akan dikirim (ini normal)
   - Cek di halaman "Monitoring Alat" untuk melihat kondisi saat ini

4. **Cek Credentials:**
   - Pastikan Token Bot dan Chat ID sudah diisi
   - Test dengan tombol "Tes Kirim Pesan"
   - Pastikan test berhasil sebelum menjalankan scheduler

5. **Cek Log:**
   ```bash
   # Windows
   type storage\logs\laravel.log | findstr "Telegram"
   
   # Akan menampilkan log seperti:
   # Telegram notification sent successfully
   # atau
   # Telegram notification skipped - kondisi baik
   ```

## 📝 Catatan Penting

1. **Scheduler Harus Berjalan:**
   - Notifikasi hanya akan dikirim jika `php artisan schedule:work` berjalan
   - Scheduler berjalan di background, tidak tergantung halaman yang dibuka

2. **Interval 5 Menit:**
   - Notifikasi dikirim setiap 5 menit sekali
   - Hanya jika kondisi kandang **PERHATIAN** atau **BURUK**
   - Jika kondisi **BAIK**, notifikasi tidak dikirim (ini normal)

3. **Toggle On/Off:**
   - Toggle di "Manajemen Informasi" mengontrol enable/disable notifikasi
   - Jika OFF, scheduler tetap berjalan tapi tidak mengirim notifikasi

4. **Test Manual:**
   - Gunakan `php artisan telegram:send-monitoring` untuk test manual
   - Command ini akan mengirim notifikasi langsung, tidak peduli kondisi kandang
   - Berguna untuk testing

## 🎯 Ringkasan

1. ✅ Toggle ON di "Manajemen Informasi"
2. ✅ Token Bot & Chat ID sudah diisi dan di-test
3. ✅ Kondisi kandang PERHATIAN atau BURUK (bukan BAIK)
4. ✅ Jalankan `php artisan schedule:work` (atau double-click `START_TELEGRAM_NOTIFICATIONS.bat`)
5. ✅ Jangan tutup terminal scheduler
6. ✅ Tunggu maksimal 5 menit, notifikasi akan terkirim

Jika semua sudah dilakukan tapi masih tidak ada notifikasi, cek log untuk melihat detail error.

