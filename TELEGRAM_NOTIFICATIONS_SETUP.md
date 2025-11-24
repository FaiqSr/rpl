# Setup Notifikasi Telegram Otomatis

## 📋 Overview

Sistem notifikasi Telegram otomatis akan mengirim laporan monitoring kandang setiap **5 menit sekali**, bahkan saat Anda membuka halaman dashboard admin yang lain.

## 🚀 Cara Mengaktifkan

### 1. Konfigurasi Telegram

1. Buka halaman **"Manajemen Informasi"** di dashboard admin
2. Isi **Token Bot** dan **Chat ID Pengguna**
3. Klik **"Tes Kirim Pesan"** untuk memastikan koneksi berhasil
4. Klik **"Simpan"** untuk menyimpan pengaturan

### 2. Aktifkan Notifikasi Otomatis

1. Di halaman **"Manajemen Informasi"**, cari bagian **"Notifikasi Otomatis"**
2. Aktifkan toggle switch **"Kirim Notifikasi Setiap 5 Menit"**
3. Status akan berubah menjadi **"Aktif"** (hijau)

### 3. Jalankan Laravel Scheduler

**PENTING:** Laravel Scheduler harus berjalan agar notifikasi otomatis berfungsi!

#### Windows (Laragon):
```bash
# Double-click file ini:
START_TELEGRAM_NOTIFICATIONS.bat

# Atau jalankan manual:
cd C:\laragon\www\GitHub\rpl
php artisan schedule:work
```

#### Linux/Mac:
```bash
cd /path/to/project/rpl
php artisan schedule:work
```

#### Production (Cron Job):
Tambahkan ke crontab:
```bash
* * * * * cd /path/to/project/rpl && php artisan schedule:run >> /dev/null 2>&1
```

## ✅ Verifikasi

### Test Command Manual:
```bash
php artisan telegram:send-monitoring
```

Jika berhasil, Anda akan melihat:
```
✅ Telegram notification sent successfully at 2025-01-XX XX:XX:XX
```

### Cek Log:
```bash
# Windows
type storage\logs\laravel.log | findstr "Telegram"

# Linux/Mac
tail -f storage/logs/laravel.log | grep Telegram
```

## 🔧 Troubleshooting

### Notifikasi tidak terkirim:

1. **Cek Scheduler Berjalan:**
   - Pastikan `php artisan schedule:work` sedang berjalan
   - Cek terminal tidak ada error

2. **Cek Toggle Aktif:**
   - Buka "Manajemen Informasi"
   - Pastikan toggle switch **ON** (hijau)
   - Status harus menunjukkan **"Aktif"**

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
   - Pastikan ada minimal 30 data sensor di database
   - Jalankan: `php artisan sensor:generate-bulk 30` jika kurang

### Error "Telegram credentials not configured":
- Pastikan Token Bot dan Chat ID sudah disimpan
- Clear config cache: `php artisan config:clear`

### Error "Insufficient sensor data":
- Generate data sensor: `php artisan sensor:generate-bulk 30`
- Atau tunggu sampai ada 30 data (jika menggunakan scheduler sensor)

## 📝 Catatan Penting

1. **Scheduler Harus Berjalan:**
   - Notifikasi hanya akan dikirim jika `php artisan schedule:work` berjalan
   - Scheduler berjalan di background, tidak tergantung halaman yang dibuka

2. **Toggle On/Off:**
   - Toggle di "Manajemen Informasi" mengontrol enable/disable notifikasi
   - Jika OFF, scheduler tetap berjalan tapi tidak mengirim notifikasi

3. **Interval 5 Menit:**
   - Notifikasi dikirim setiap 5 menit sekali
   - Tidak tergantung halaman dashboard yang dibuka
   - Berjalan di background via Laravel Scheduler

4. **Format Notifikasi:**
   - Status kandang (BAIK/PERHATIAN/BURUK)
   - Nilai sensor saat ini
   - Prediksi 6 jam ke depan
   - Anomali yang terdeteksi
   - Timestamp WIB

## 🎯 Fitur

- ✅ Notifikasi otomatis setiap 5 menit
- ✅ Berjalan di background (tidak perlu buka halaman monitoring)
- ✅ Toggle on/off di Manajemen Informasi
- ✅ Status real-time (Aktif/Nonaktif)
- ✅ Test manual via command
- ✅ Logging untuk debugging

