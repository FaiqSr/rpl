# 📋 Panduan Setup Hosting - ChickPatrol

Dokumentasi lengkap untuk deploy aplikasi ChickPatrol ke server hosting.

---

## 📌 Daftar Isi

1. [Persiapan Sebelum Deploy](#persiapan-sebelum-deploy)
2. [Setup Cron Job untuk Scheduler](#setup-cron-job-untuk-scheduler)
3. [Konfigurasi Environment](#konfigurasi-environment)
4. [Setup Permissions](#setup-permissions)
5. [Optimasi untuk Mobile](#optimasi-untuk-mobile)
6. [Testing Checklist](#testing-checklist)
7. [Troubleshooting](#troubleshooting)

---

## 🚀 Persiapan Sebelum Deploy

### 1. File yang Perlu Diupload ke Server

```
✅ Semua file aplikasi (kecuali node_modules, .git)
✅ File .env (atau buat baru di server)
✅ Folder storage/ dan bootstrap/cache/ (kosong, akan diisi otomatis)
```

### 2. File yang TIDAK Perlu Diupload

```
❌ node_modules/
❌ .git/
❌ .env.example (opsional, bisa diupload untuk referensi)
❌ START_TELEGRAM_NOTIFICATIONS.bat (hanya untuk Windows lokal)
❌ START_SENSOR_SCHEDULER.bat (hanya untuk Windows lokal)
```

### 3. Requirements Server

- PHP >= 8.1
- Composer
- MySQL/MariaDB
- Extension PHP: PDO, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo
- Cron Job support (untuk scheduler)

---

## ⏰ Setup Cron Job untuk Scheduler

### ⚠️ PENTING: File .bat TIDAK Berjalan di Hosting!

File `START_TELEGRAM_NOTIFICATIONS.bat` hanya untuk development lokal di Windows. Di server hosting, Anda **HARUS** setup cron job.

### Cara Setup Cron Job

#### A. Via cPanel

1. Login ke cPanel
2. Buka **Cron Jobs**
3. Tambahkan cron job baru:
   - **Minute**: `*`
   - **Hour**: `*`
   - **Day**: `*`
   - **Month**: `*`
   - **Weekday**: `*`
   - **Command**: 
     ```bash
     cd /home/username/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
     ```
   - Ganti `/home/username/public_html` dengan path aplikasi Anda
   - Ganti `/usr/bin/php` dengan path PHP di server (cek via `which php`)

#### B. Via SSH (VPS/Dedicated Server)

1. Login via SSH:
   ```bash
   ssh username@your-server.com
   ```

2. Edit crontab:
   ```bash
   crontab -e
   ```

3. Tambahkan baris ini:
   ```bash
   * * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
   ```
   Ganti `/var/www/html` dengan path aplikasi Anda.

4. Simpan dan keluar (di nano: `Ctrl+X`, lalu `Y`, lalu `Enter`)

5. Verifikasi cron job sudah terpasang:
   ```bash
   crontab -l
   ```

#### C. Via Plesk

1. Login ke Plesk
2. Buka **Scheduled Tasks**
3. Klik **Add Task**
4. Isi:
   - **Run**: `php`
   - **Arguments**: `artisan schedule:run`
   - **Working directory**: Path aplikasi Anda
   - **Run**: `Every minute`

### Verifikasi Scheduler Berjalan

1. Tunggu beberapa menit setelah setup cron job
2. Cek log scheduler:
   ```bash
   tail -f storage/logs/telegram-scheduler.log
   ```
   atau
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. Test manual (via SSH):
   ```bash
   php artisan schedule:run
   ```

---

## ⚙️ Konfigurasi Environment

### 1. File .env di Server

Buat atau edit file `.env` di root aplikasi dengan konfigurasi berikut:

```env
APP_NAME=ChickPatrol
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username_database
DB_PASSWORD=password_database

# Telegram Configuration
TELEGRAM_NOTIFICATIONS_ENABLED=true
TELEGRAM_BOT_TOKEN=your_telegram_bot_token
TELEGRAM_CHAT_ID=your_telegram_chat_id

# Mail Configuration (jika menggunakan email)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. Generate APP_KEY

Jika belum ada, generate APP_KEY:
```bash
php artisan key:generate
```

### 3. Clear Cache

Setelah update .env, clear semua cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

---

## 🔐 Setup Permissions

### Set Permissions untuk Storage dan Cache

```bash
# Masuk ke folder aplikasi
cd /path/to/your/app

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Atau jika user berbeda (cek dengan: ps aux | grep php)
chown -R apache:apache storage bootstrap/cache
```

### Buat Folder Storage Jika Belum Ada

```bash
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
chmod -R 775 storage
```

---

## 📱 Optimasi untuk Mobile

### Checklist Responsive Design

#### ✅ Viewport Meta Tag
Pastikan semua halaman memiliki:
```html
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
```

#### ✅ Media Queries
Pastikan ada media queries untuk:
- Mobile: `@media (max-width: 768px)`
- Mobile kecil: `@media (max-width: 640px)`

#### ✅ Touch-Friendly Elements
- Button minimal 44x44px
- Link mudah diklik
- Form input font minimal 16px (hindari auto-zoom iOS)

#### ✅ Images
- Gunakan `max-width: 100%` dan `height: auto`
- Lazy loading untuk performa
- Format WebP jika memungkinkan

#### ✅ Tables
- Horizontal scroll untuk tabel besar
- Atau ubah ke card view di mobile

### Testing Mobile

1. **Chrome DevTools**:
   - F12 → Toggle device toolbar (Ctrl+Shift+M)
   - Test berbagai ukuran: iPhone, iPad, Android

2. **Browser Mobile**:
   - Buka langsung dari HP
   - Test di berbagai browser: Chrome, Safari, Firefox

3. **Online Tools**:
   - [Responsive Design Checker](https://responsivedesignchecker.com/)
   - [BrowserStack](https://www.browserstack.com/)

---

## ✅ Testing Checklist

### Pre-Deployment

- [ ] Semua file terupload dengan benar
- [ ] File .env dikonfigurasi dengan benar
- [ ] APP_KEY sudah di-generate
- [ ] Database connection berhasil
- [ ] Permissions storage dan cache sudah benar

### Post-Deployment

- [ ] Website bisa diakses via domain
- [ ] Login/Register berfungsi
- [ ] Halaman utama load dengan benar
- [ ] Produk bisa dilihat dan dicari
- [ ] Checkout dan payment berfungsi
- [ ] Dashboard admin bisa diakses
- [ ] Responsive di mobile device
- [ ] Images load dengan benar
- [ ] Form submission berfungsi

### Scheduler & Notifications

- [ ] Cron job sudah terpasang
- [ ] Scheduler berjalan (cek log)
- [ ] Notifikasi Telegram terkirim (test manual)
- [ ] Sensor data ter-generate otomatis

### Performance

- [ ] Page load time < 3 detik
- [ ] Images optimized
- [ ] CSS dan JS minified (jika production)
- [ ] Cache enabled

---

## 🔧 Troubleshooting

### Problem: Scheduler Tidak Berjalan

**Solusi:**
1. Cek cron job sudah terpasang: `crontab -l`
2. Cek path PHP benar: `which php`
3. Cek path aplikasi benar
4. Cek permissions file artisan: `chmod +x artisan`
5. Test manual: `php artisan schedule:run`

### Problem: Notifikasi Telegram Tidak Terkirim

**Solusi:**
1. Cek `.env`:
   - `TELEGRAM_NOTIFICATIONS_ENABLED=true`
   - `TELEGRAM_BOT_TOKEN` sudah benar
   - `TELEGRAM_CHAT_ID` sudah benar
2. Cek log: `storage/logs/telegram-scheduler.log`
3. Test manual:
   ```bash
   php artisan telegram:send-monitoring
   ```

### Problem: Permission Denied

**Solusi:**
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Problem: 500 Internal Server Error

**Solusi:**
1. Cek log: `storage/logs/laravel.log`
2. Cek `.env` APP_DEBUG=true sementara untuk melihat error
3. Clear cache: `php artisan config:clear`
4. Cek permissions storage dan cache

### Problem: Database Connection Error

**Solusi:**
1. Cek kredensial database di `.env`
2. Cek database server running
3. Cek firewall/security group (jika cloud hosting)
4. Test connection:
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```

### Problem: Images Tidak Load

**Solusi:**
1. Cek symlink storage:
   ```bash
   php artisan storage:link
   ```
2. Cek permissions folder storage
3. Cek path di `.env`: `APP_URL` harus benar

---

## 📞 Support

Jika mengalami masalah yang tidak teratasi:

1. Cek log file: `storage/logs/laravel.log`
2. Cek log scheduler: `storage/logs/telegram-scheduler.log`
3. Enable debug sementara: `APP_DEBUG=true` di `.env`
4. Dokumentasikan error message lengkap

---

## 📝 Catatan Penting

1. **JANGAN** commit file `.env` ke repository
2. **JANGAN** set `APP_DEBUG=true` di production
3. **SELALU** backup database sebelum update
4. **SELALU** test di staging environment dulu
5. **MONITOR** log files secara berkala

---

**Last Updated:** {{ date('Y-m-d') }}
**Version:** 1.0

