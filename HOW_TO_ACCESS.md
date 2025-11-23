# 🌐 Cara Membuka Website ChickPatrol

## 🚀 Opsi 1: Menggunakan Laragon (Recommended)

Karena Anda menggunakan **Laragon**, website sudah otomatis bisa diakses:

### URL Website:
```
http://localhost/rpl
```

atau

```
http://rpl.test
```

(tergantung konfigurasi virtual host Laragon Anda)

### Langkah:
1. **Pastikan Laragon running** (Apache/Nginx + MySQL)
2. **Buka browser**
3. **Akses:** `http://localhost/rpl` atau `http://rpl.test`

---

## 🔧 Opsi 2: Menggunakan PHP Built-in Server

Jika Laragon tidak digunakan atau ingin port khusus:

### Jalankan Server:
```bash
cd c:\laragon\www\GitHub\rpl
php artisan serve
```

### URL Website:
```
http://localhost:8000
```

**Catatan:** Terminal harus tetap terbuka selama menggunakan website.

---

## 📍 Halaman-Halaman Website

### 1. Homepage (Public)
```
http://localhost/rpl
atau
http://localhost:8000
```
- Menampilkan semua produk
- Bisa browsing tanpa login

### 2. Login
```
http://localhost/rpl/login
atau
http://localhost:8000/login
```

### 3. Register
```
http://localhost/rpl/register
atau
http://localhost:8000/register
```

### 4. Dashboard Admin (Butuh Login Admin)
```
http://localhost/rpl/dashboard
atau
http://localhost:8000/dashboard
```

### 5. Monitoring Alat (Butuh Login Admin)
```
http://localhost/rpl/dashboard/tools/monitoring
atau
http://localhost:8000/dashboard/tools/monitoring
```
**Ini halaman yang menampilkan hasil ML!**

---

## ✅ Checklist Sebelum Buka Website

### 1. Pastikan ML Service Running
**PENTING:** ML Service harus running untuk monitoring!

```bash
# Double-click:
ml_service/START_ML_SERVICE.bat
```

Atau test:
```
http://localhost:5000/health
```

### 2. Pastikan Laragon Running
- Apache/Nginx harus running
- MySQL harus running
- Database `rpl_db` sudah ada

### 3. Pastikan Konfigurasi
- File `.env` sudah ada
- `ML_SERVICE_URL=http://localhost:5000` sudah set
- Database connection sudah benar

---

## 🧪 Test Website

### Test 1: Homepage
```
http://localhost/rpl
```
Harus menampilkan daftar produk.

### Test 2: Login
```
http://localhost/rpl/login
```
Login dengan akun admin untuk akses dashboard.

### Test 3: Dashboard Monitoring
```
http://localhost/rpl/dashboard/tools/monitoring
```
Harus menampilkan:
- ✅ Card Informasi ML (jika service running)
- ✅ Sensor cards
- ✅ Grafik prediksi
- ✅ Status kandang

---

## 🔧 Troubleshooting

### Website Tidak Bisa Diakses

1. **Cek Laragon:**
   - Pastikan Apache/Nginx running
   - Cek port 80 tidak digunakan aplikasi lain

2. **Cek Database:**
   - MySQL harus running
   - Database `rpl_db` harus ada

3. **Cek .env:**
   - File `.env` harus ada
   - Database config harus benar

### Error 500 atau Blank Page

1. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

2. **Cek log:**
   ```bash
   # Lihat error di:
   storage/logs/laravel.log
   ```

3. **Cek permission:**
   - Folder `storage` dan `bootstrap/cache` harus writable

### Monitoring Tidak Menampilkan ML Info

1. **Pastikan ML Service running:**
   ```
   http://localhost:5000/health
   ```

2. **Cek .env:**
   ```env
   ML_SERVICE_URL=http://localhost:5000
   ```

3. **Clear cache:**
   ```bash
   php artisan config:clear
   ```

---

## 🎯 Quick Start

1. **Start Laragon** (Apache + MySQL)

2. **Start ML Service:**
   ```
   Double-click: ml_service/START_ML_SERVICE.bat
   ```

3. **Buka Browser:**
   ```
   http://localhost/rpl
   ```

4. **Login Admin:**
   ```
   http://localhost/rpl/login
   ```

5. **Akses Monitoring:**
   ```
   http://localhost/rpl/dashboard/tools/monitoring
   ```

**Selamat! Website ChickPatrol siap digunakan! 🚀**

