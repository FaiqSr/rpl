# 🚀 Cara Menjalankan Notifikasi Telegram Otomatis

## ⚠️ PENTING: Scheduler Harus Berjalan!

Notifikasi Telegram **TIDAK AKAN** berfungsi jika Laravel Scheduler tidak berjalan. Scheduler harus berjalan di **background/terminal terpisah**.

## 📋 Langkah-langkah

### 1. Pastikan Toggle Aktif
- Buka halaman **"Manajemen Informasi"**
- Pastikan toggle **"Kirim Notifikasi Setiap 5 Menit"** **ON** (hijau)
- Status harus menunjukkan **"Aktif"**

### 2. Jalankan Laravel Scheduler

#### Windows (Laragon):
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

#### Linux/Mac:
```bash
cd /path/to/project/rpl
php artisan schedule:work
```

### 3. Verifikasi Scheduler Berjalan

Setelah menjalankan `php artisan schedule:work`, Anda akan melihat output seperti:
```
Running scheduled tasks every minute.
```

Scheduler akan terus berjalan di terminal tersebut. **JANGAN TUTUP TERMINAL** jika ingin notifikasi tetap berjalan.

### 4. Test Notifikasi Manual

Buka terminal **BARU** (jangan tutup yang menjalankan scheduler) dan jalankan:
```bash
cd C:\laragon\www\GitHub\rpl
php artisan telegram:send-monitoring
```

Jika berhasil, Anda akan melihat:
```
✅ Telegram notification sent successfully at 2025-11-24 XX:XX:XX
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

3. **Cek Credentials:**
   - Pastikan Token Bot dan Chat ID sudah diisi
   - Test dengan tombol "Tes Kirim Pesan"

4. **Cek .env:**
   ```env
   TELEGRAM_BOT_TOKEN=your_token_here
   TELEGRAM_CHAT_ID=your_chat_id_here
   TELEGRAM_NOTIFICATIONS_ENABLED=true
   ```

5. **Cek Data Sensor:**
   - Pastikan ada minimal 30 data sensor
   - Jika kurang: `php artisan sensor:generate-bulk 30`

6. **Cek Log:**
   ```bash
   # Windows
   type storage\logs\laravel.log | findstr "Telegram"
   
   # Linux/Mac
   tail -f storage/logs/laravel.log | grep Telegram
   ```

### Error "Telegram credentials not configured":
- Pastikan Token Bot dan Chat ID sudah disimpan di "Manajemen Informasi"
- Clear config: `php artisan config:clear`

### Error "Insufficient sensor data":
- Generate data: `php artisan sensor:generate-bulk 30`
- Atau tunggu sampai ada 30 data (jika menggunakan scheduler sensor)

## 📝 Catatan Penting

1. **Scheduler = Background Service:**
   - Scheduler harus berjalan di terminal terpisah
   - Tidak tergantung halaman dashboard yang dibuka
   - Berjalan terus menerus di background

2. **Interval 5 Menit:**
   - Notifikasi dikirim setiap 5 menit sekali
   - Tidak perlu buka halaman monitoring
   - Berjalan otomatis via scheduler

3. **Toggle On/Off:**
   - Toggle mengontrol enable/disable notifikasi
   - Jika OFF, scheduler tetap berjalan tapi tidak mengirim notifikasi
   - Jika ON, notifikasi akan dikirim setiap 5 menit

4. **Multiple Terminals:**
   - Terminal 1: Jalankan `php artisan schedule:work` (jangan tutup)
   - Terminal 2: Untuk test command atau lainnya

## 🎯 Quick Start

1. Buka "Manajemen Informasi" → Aktifkan toggle
2. Double-click `START_TELEGRAM_NOTIFICATIONS.bat`
3. Biarkan terminal terbuka
4. Notifikasi akan dikirim setiap 5 menit!

## ✅ Checklist

- [ ] Token Bot dan Chat ID sudah diisi
- [ ] Toggle "Kirim Notifikasi Setiap 5 Menit" **ON**
- [ ] Status menunjukkan **"Aktif"**
- [ ] Scheduler berjalan (`php artisan schedule:work`)
- [ ] Terminal scheduler tidak ditutup
- [ ] Ada minimal 30 data sensor di database
- [ ] Test command berhasil: `php artisan telegram:send-monitoring`

