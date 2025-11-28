# 🚀 Cara Menjalankan Notifikasi Telegram Otomatis

## ⚠️ PENTING: Scheduler Harus Berjalan!

Notifikasi Telegram **TIDAK AKAN** berfungsi jika Laravel Scheduler tidak berjalan. Scheduler harus berjalan di **background/terminal terpisah**.

## 📋 Checklist Sebelum Menjalankan

1. ✅ **Toggle Aktif** - Di halaman "Manajemen Informasi", toggle harus **ON** (hijau)
2. ✅ **Token Bot & Chat ID** - Sudah diisi dan di-test (tombol "Tes Kirim Pesan" berhasil)
3. ✅ **ML Service Berjalan** - Service ML harus aktif di `http://127.0.0.1:5000`

## 🕐 Interval Notifikasi (BARU!)

Sistem notifikasi sekarang menggunakan **interval dinamis** berdasarkan kondisi kandang:

### 📊 Kondisi BAIK (Normal)
- **Interval**: 1 jam sekali
- **Tujuan**: Laporan rutin untuk monitoring berkala
- **Pesan**: Notifikasi normal dengan status "KONDISI NORMAL - MONITORING RUTIN"

### ⚠️ Kondisi PERHATIAN atau BURUK (Urgent)
- **Interval**: 5 menit sekali
- **Jumlah**: Maksimal 5 kali berturut-turut
- **Setelah 5 kali**: Tunggu 5 menit, lalu mulai cycle baru (5 kali lagi)
- **Tujuan**: Alert cepat untuk tindakan segera
- **Pesan**: Notifikasi urgent dengan badge prioritas
  - BURUK: "🚨 PRIORITAS TINGGI - TINDAKAN SEGERA DIPERLUKAN"
  - PERHATIAN: "⚠️ PERHATIAN - MONITORING KETAT DIPERLUKAN"

### 📝 Contoh Skenario:

**Skenario 1: Kondisi BAIK**
```
10:00 - Notifikasi dikirim (Status: BAIK)
11:00 - Notifikasi dikirim (Status: BAIK) 
12:00 - Notifikasi dikirim (Status: BAIK)
...
```

**Skenario 2: Kondisi BURUK**
```
10:00 - Notifikasi urgent #1 (Status: BURUK)
10:05 - Notifikasi urgent #2 (Status: BURUK)
10:10 - Notifikasi urgent #3 (Status: BURUK)
10:15 - Notifikasi urgent #4 (Status: BURUK)
10:20 - Notifikasi urgent #5 (Status: BURUK)
10:25 - Tunggu... (sudah 5 kali)
10:30 - Notifikasi urgent #1 (Cycle baru - Status: BURUK)
...
```

**Skenario 3: Kondisi Membaik**
```
10:00 - Notifikasi urgent #1 (Status: BURUK)
10:05 - Notifikasi urgent #2 (Status: BURUK)
10:10 - Status berubah BAIK - keluar dari mode urgent
11:10 - Notifikasi rutin (Status: BAIK) - interval 1 jam
```

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

3. **Cek ML Service:**
   - ML Service harus berjalan di `http://127.0.0.1:5000`
   - Jalankan: `cd ml_service && python app.py`
   - Pastikan tidak ada error saat service start

4. **Cek Interval Notifikasi:**
   - **Kondisi BAIK**: Notifikasi dikirim setiap 1 jam
   - **Kondisi PERHATIAN/BURUK**: Notifikasi dikirim setiap 5 menit (maks 5 kali)
   - Tunggu sesuai interval yang berlaku

5. **Cek Credentials:**
   - Pastikan Token Bot dan Chat ID sudah diisi
   - Test dengan tombol "Tes Kirim Pesan"
   - Pastikan test berhasil sebelum menjalankan scheduler

6. **Cek Log:**
   ```bash
   # Windows - Cek log scheduler
   type storage\logs\telegram-scheduler.log
   
   # Cek log Laravel
   type storage\logs\laravel.log | findstr "Telegram"
   
   # Akan menampilkan log seperti:
   # Telegram notification sent successfully [Urgent 1/5]
   # atau
   # Tunggu 1 jam sejak notifikasi terakhir (25.3 menit)
   ```

### Mode Urgent tidak berfungsi:

1. **Verifikasi status kandang**: Pastikan kondisi benar-benar PERHATIAN atau BURUK
2. **Cek state file**: File `storage/app/telegram_notification_state.json` menyimpan state urgent mode
3. **Reset state**: Hapus file state untuk reset counter urgent:
   ```bash
   del storage\app\telegram_notification_state.json
   ```

## 📝 Catatan Penting

1. **Scheduler Harus Berjalan:**
   - Notifikasi hanya akan dikirim jika `php artisan schedule:work` berjalan
   - Scheduler berjalan di background, tidak tergantung halaman yang dibuka

2. **Interval Dinamis (BARU!):**
   - **Kondisi BAIK**: 1 jam sekali (laporan rutin)
   - **Kondisi PERHATIAN/BURUK**: 5 menit sekali, maksimal 5 kali berturut-turut
   - Setelah 5 kali notifikasi urgent, tunggu 5 menit untuk mulai cycle baru
   - Jika kondisi membaik (BURUK/PERHATIAN → BAIK), otomatis keluar dari mode urgent

3. **Toggle On/Off:**
   - Toggle di "Manajemen Informasi" mengontrol enable/disable notifikasi
   - Jika OFF, scheduler tetap berjalan tapi tidak mengirim notifikasi

4. **Test Manual:**
   - Gunakan `php artisan telegram:send-monitoring` untuk test manual
   - Command ini akan kirim notifikasi langsung sesuai kondisi kandang saat ini
   - Berguna untuk testing tanpa menunggu interval

5. **Format Pesan Baru:**
   - Pesan sekarang lebih informatif dengan badge prioritas
   - Rekomendasi lebih spesifik berdasarkan sensor yang bermasalah
   - Kondisi BURUK: Mendapat badge "🚨 PRIORITAS TINGGI"
   - Kondisi PERHATIAN: Mendapat badge "⚠️ PERHATIAN"
   - Kondisi BAIK: Mendapat badge "✅ KONDISI NORMAL"

## 🎯 Ringkasan

1. ✅ Toggle ON di "Manajemen Informasi"
2. ✅ Token Bot & Chat ID sudah diisi dan di-test
3. ✅ ML Service berjalan di `http://127.0.0.1:5000`
4. ✅ Jalankan `php artisan schedule:work` (atau double-click `START_TELEGRAM_NOTIFICATIONS.bat`)
5. ✅ Jangan tutup terminal scheduler
6. ✅ Notifikasi akan dikirim sesuai kondisi:
   - BAIK: Setiap 1 jam
   - PERHATIAN/BURUK: Setiap 5 menit (maks 5 kali)

Jika semua sudah dilakukan tapi masih tidak ada notifikasi, cek log untuk melihat detail error.

