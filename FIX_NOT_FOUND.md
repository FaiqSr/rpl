# 🔧 Fix Error "Not Found" - Website ChickPatrol

## ❌ Error: The requested URL was not found on this server

Error ini biasanya terjadi karena:
1. URL yang digunakan salah
2. Laragon virtual host belum dikonfigurasi
3. Document root tidak mengarah ke folder `public`

## ✅ Solusi: Gunakan PHP Artisan Serve (Paling Mudah)

### Langkah 1: Jalankan PHP Built-in Server

Buka terminal dan jalankan:

```bash
cd c:\laragon\www\GitHub\rpl
php artisan serve
```

### Langkah 2: Akses Website

Buka browser dan akses:
```
http://localhost:8000
```

**Catatan:** Terminal harus tetap terbuka selama menggunakan website.

---

## 🔧 Solusi Alternatif: Konfigurasi Laragon

Jika ingin menggunakan Laragon (Apache/Nginx):

### 1. Cek Document Root

Pastikan Document Root Laragon mengarah ke:
```
C:\laragon\www\GitHub\rpl\public
```

**BUKAN:**
```
C:\laragon\www\GitHub\rpl
```

### 2. Konfigurasi Virtual Host

Edit file virtual host Laragon (biasanya di `C:\laragon\etc\apache2\sites-enabled\`):

```apache
<VirtualHost *:80>
    ServerName rpl.test
    DocumentRoot "C:/laragon/www/GitHub/rpl/public"
    
    <Directory "C:/laragon/www/GitHub/rpl/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Restart Apache

Restart Apache di Laragon.

### 4. Akses Website

```
http://rpl.test
atau
http://localhost/rpl
```

---

## 🚀 Quick Fix (Recommended)

**Gunakan PHP Artisan Serve - Paling Mudah!**

### Double-Click Script:

Saya sudah membuat script untuk Anda:
```
START_WEBSITE.bat
```

Atau manual:

```bash
cd c:\laragon\www\GitHub\rpl
php artisan serve
```

Kemudian buka:
```
http://localhost:8000
```

---

## 📍 URL yang Benar

### Menggunakan PHP Artisan Serve:
```
http://localhost:8000              → Homepage
http://localhost:8000/login        → Login
http://localhost:8000/dashboard    → Dashboard
http://localhost:8000/dashboard/tools/monitoring → Monitoring (dengan ML)
```

### Menggunakan Laragon (jika sudah dikonfigurasi):
```
http://rpl.test                     → Homepage
http://rpl.test/login              → Login
http://rpl.test/dashboard          → Dashboard
http://rpl.test/dashboard/tools/monitoring → Monitoring
```

---

## ✅ Checklist

- [ ] PHP artisan serve running (terminal tidak close)
- [ ] Test: `http://localhost:8000` → harus tampil homepage
- [ ] ML Service running (untuk monitoring)
- [ ] Database connection OK

---

## 🎯 Langkah Lengkap

1. **Start ML Service:**
   ```
   Double-click: ml_service/START_ML_SERVICE.bat
   ```

2. **Start Laravel Server:**
   ```bash
   cd c:\laragon\www\GitHub\rpl
   php artisan serve
   ```

3. **Buka Browser:**
   ```
   http://localhost:8000
   ```

4. **Login Admin (jika perlu):**
   ```
   http://localhost:8000/login
   ```

5. **Akses Monitoring:**
   ```
   http://localhost:8000/dashboard/tools/monitoring
   ```

**Gunakan `php artisan serve` untuk development - lebih mudah dan tidak perlu konfigurasi!** 🚀

