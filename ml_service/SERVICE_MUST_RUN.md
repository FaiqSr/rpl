# ⚠️ PENTING: ML Service Harus Running!

## ❌ Error: ERR_CONNECTION_REFUSED

Error ini muncul karena **ML Service tidak berjalan** atau sudah stop.

## ✅ Solusi: Jalankan ML Service

### Cara 1: Double-Click Script (Paling Mudah)

**Double-click file ini:**
```
ml_service/START_ML_SERVICE.bat
```

**ATAU**

```
ml_service/run_background.bat
```

### Cara 2: Dari Terminal

Buka terminal baru dan jalankan:

```bash
cd ml_service
python app.py
```

## 🔍 Verifikasi Service Running

Setelah menjalankan, test di browser:
```
http://localhost:5000/health
```

Harus return JSON:
```json
{
  "status": "ok",
  "models_loaded": true
}
```

## ⚠️ CATATAN PENTING

### 1. Service Harus Tetap Running
- **JANGAN TUTUP TERMINAL** yang menjalankan service
- Service harus tetap berjalan selama menggunakan dashboard
- Jika terminal ditutup, service akan stop

### 2. Service Harus Running Sebelum Akses Dashboard
- Laravel membutuhkan service untuk prediksi ML
- Jika service tidak running, dashboard akan error atau fallback

### 3. Untuk Development
- Biarkan terminal service tetap terbuka
- Gunakan terminal terpisah untuk command lain

### 4. Untuk Production (Nanti)
- Setup sebagai Windows Service
- Atau gunakan gunicorn/uwsgi
- Atau gunakan PM2 untuk process management

## 🚀 Quick Start

1. **Jalankan service:**
   ```
   Double-click: ml_service/START_ML_SERVICE.bat
   ```

2. **Biarkan terminal tetap terbuka**

3. **Test service:**
   ```
   Browser: http://localhost:5000/health
   ```

4. **Buka dashboard Laravel:**
   ```
   Dashboard → Alat → Monitoring Alat
   ```

## 🔧 Troubleshooting

### Service Start Tapi Langsung Stop
- Cek error di terminal
- Pastikan semua model file ada
- Install dependencies: `pip install -r requirements.txt`

### Port 5000 Sudah Digunakan
Edit `app.py` baris terakhir:
```python
app.run(host='0.0.0.0', port=5001, debug=True)
```
Update `.env`:
```env
ML_SERVICE_URL=http://localhost:5001
```

### Service Running Tapi Dashboard Error
1. Test: `curl http://localhost:5000/health`
2. Cek `.env`: `ML_SERVICE_URL=http://localhost:5000`
3. Clear cache: `php artisan config:clear`

## ✅ Checklist

- [ ] Service running (terminal tidak close)
- [ ] Test health: `http://localhost:5000/health` → OK
- [ ] `.env` sudah set: `ML_SERVICE_URL=http://localhost:5000`
- [ ] Laravel cache cleared
- [ ] Dashboard bisa akses service

**Setelah service running, refresh dashboard!** 🚀

