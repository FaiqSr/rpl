# 🚀 Setup ML Service - Quick Start

## ⚠️ Penting: ML Service Harus Berjalan!

Sistem monitoring menggunakan **Random Forest**, **LSTM**, dan **Isolation Forest** dari ML service Python. Jika ML service tidak berjalan, sistem akan menggunakan **prediksi sederhana (fallback)**.

## ✅ Cara Menjalankan ML Service

### Opsi 1: Menggunakan Script (Paling Mudah)

1. **Double-click file:** `START_ML_SERVICE.bat`
2. **Tunggu hingga muncul pesan:** "Service akan berjalan di: http://localhost:5000"
3. **JANGAN TUTUP WINDOW INI!** Service harus tetap running.

### Opsi 2: Manual

```bash
cd ml_service
python app.py
```

## 🔍 Cara Cek Koneksi ML Service

### Opsi 1: Menggunakan Script

**Double-click file:** `CHECK_ML_CONNECTION.bat`

### Opsi 2: Manual

```bash
# Test dari browser
http://localhost:5000/health

# Atau dari terminal
curl http://localhost:5000/health
```

**Response yang benar:**
```json
{
  "status": "ok",
  "models_loaded": true
}
```

## ✅ Verifikasi di Dashboard

Setelah ML service berjalan, refresh halaman monitoring. Anda akan melihat:

1. **Card "Informasi Model Machine Learning":**
   - ✅ Status: **"Terhubung ke ML Service"** (badge hijau)
   - ✅ Model: **"Monitoring Kandang Ayam - broiler_theory_proportional_v2"**
   - ✅ Confidence: **"high"** atau **"medium"** (bukan "low")

2. **Console Browser (F12):**
   - `ML Connected: true`
   - `ML Source: ml_service` (bukan "fallback")
   - `Model Name: Monitoring Kandang Ayam - broiler_theory_proportional_v2`

## ❌ Troubleshooting

### Masalah: "Menggunakan Prediksi Sederhana"

**Penyebab:** ML service tidak berjalan atau tidak terhubung.

**Solusi:**
1. Cek apakah ML service berjalan:
   ```bash
   netstat -ano | findstr :5000
   ```
   Jika tidak ada output, ML service tidak berjalan.

2. Start ML service:
   - Double-click `START_ML_SERVICE.bat`
   - Atau jalankan: `cd ml_service && python app.py`

3. Cek `.env`:
   ```env
   ML_SERVICE_URL=http://localhost:5000
   ```

4. Clear Laravel cache:
   ```bash
   php artisan config:clear
   ```

5. Refresh halaman monitoring.

### Masalah: "Python tidak ditemukan"

**Solusi:**
1. Install Python 3.8+ dari https://www.python.org/
2. Pastikan Python ada di PATH
3. Test: `python --version`

### Masalah: "Dependencies belum terinstall"

**Solusi:**
```bash
cd ml_service
pip install -r requirements.txt
```

### Masalah: "Port 5000 sudah digunakan"

**Solusi:**
1. Cek proses yang menggunakan port 5000:
   ```bash
   netstat -ano | findstr :5000
   ```
2. Jika ML service sudah berjalan, tidak perlu start lagi.
3. Jika ada aplikasi lain, ubah port di `ml_service/app.py` (line 1323) dan `.env`.

## 📋 Checklist

- [ ] Python 3.8+ terinstall
- [ ] Dependencies terinstall (`pip install -r ml_service/requirements.txt`)
- [ ] `.env` sudah set `ML_SERVICE_URL=http://localhost:5000`
- [ ] ML service berjalan (`START_ML_SERVICE.bat` atau `python ml_service/app.py`)
- [ ] Health check berhasil (`http://localhost:5000/health`)
- [ ] Dashboard menampilkan "Terhubung ke ML Service"

## 🎯 Hasil yang Diharapkan

Setelah ML service berjalan, sistem akan menggunakan:

- ✅ **Random Forest** untuk status classification (BAIK/PERHATIAN/BURUK)
- ✅ **LSTM Ensemble** untuk prediksi 6h dan 24h
- ✅ **Isolation Forest** untuk anomaly detection
- ✅ **Confidence** dan **probabilities** dari model ML (bukan fallback)

**Bukan lagi menggunakan prediksi sederhana!** 🚀

