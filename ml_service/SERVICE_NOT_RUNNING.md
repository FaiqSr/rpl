# ⚠️ ML Service Tidak Berjalan

## Masalah

Error: `ERR_CONNECTION_REFUSED` atau `localhost refused to connect`

Ini berarti **ML Service tidak berjalan** atau sudah stop.

## ✅ Solusi: Jalankan ML Service

### Cara 1: Menggunakan Script (Paling Mudah)

**Double-click file:**
```
ml_service/START_ML_SERVICE.bat
```

Atau dari terminal:
```bash
cd ml_service
START_ML_SERVICE.bat
```

### Cara 2: Manual

Buka terminal baru dan jalankan:

```bash
cd ml_service
python app.py
```

## 🔍 Verifikasi Service Running

Setelah menjalankan service, test dengan:

### Test 1: Browser
Buka: `http://localhost:5000/health`

Harus return JSON:
```json
{
  "status": "ok",
  "models_loaded": true,
  "timestamp": "..."
}
```

### Test 2: Terminal
```bash
curl http://localhost:5000/health
```

### Test 3: Python
```bash
cd ml_service
python test_service.py
```

## ⚠️ Catatan Penting

1. **JANGAN TUTUP TERMINAL** yang menjalankan service
   - Service harus tetap running
   - Jika close terminal, service akan stop

2. **Service harus running sebelum akses dashboard**
   - Laravel akan error jika service tidak running
   - Dashboard akan fallback ke prediksi sederhana

3. **Untuk Production:**
   - Gunakan gunicorn atau uwsgi
   - Setup sebagai Windows Service atau systemd
   - Atau gunakan PM2 untuk Node.js

## 🚀 Quick Start

1. **Jalankan service:**
   ```bash
   cd ml_service
   START_ML_SERVICE.bat
   ```

2. **Biarkan terminal tetap terbuka**

3. **Buka dashboard Laravel:**
   - Dashboard → Alat → Monitoring Alat
   - Service akan otomatis digunakan

## 🔧 Troubleshooting

### Port 5000 sudah digunakan

Edit `app.py` baris terakhir:
```python
app.run(host='0.0.0.0', port=5001, debug=True)  # Ganti port
```

Update `.env` Laravel:
```env
ML_SERVICE_URL=http://localhost:5001
```

### Service start tapi langsung stop

1. Cek error di terminal
2. Pastikan semua model file ada di `models/`
3. Cek dependencies: `pip install -r requirements.txt`

### Service running tapi Laravel tidak connect

1. Test health: `curl http://localhost:5000/health`
2. Cek `.env` Laravel: `ML_SERVICE_URL=http://localhost:5000`
3. Clear cache: `php artisan config:clear`

## ✅ Checklist

- [ ] Service running (terminal tidak close)
- [ ] Test health berhasil: `http://localhost:5000/health`
- [ ] `.env` Laravel sudah set: `ML_SERVICE_URL=http://localhost:5000`
- [ ] Laravel cache cleared: `php artisan config:clear`
- [ ] Dashboard bisa akses service

**Setelah service running, refresh dashboard dan coba lagi!** 🚀

