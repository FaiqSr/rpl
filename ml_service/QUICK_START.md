# 🚀 Quick Start Guide

## Langkah Cepat Menjalankan ML Service

### 1. Install Dependencies (Jika Belum)

```bash
pip install -r requirements.txt
```

### 2. Jalankan Service

**Windows:**
```bash
start_service.bat
```

**Atau manual:**
```bash
python app.py
```

Service akan berjalan di `http://localhost:5000`

### 3. Test Service

Buka terminal baru dan jalankan:
```bash
python test_service.py
```

Atau test manual:
```bash
# Test health
curl http://localhost:5000/health

# Test prediction (dengan test_data.json)
curl -X POST http://localhost:5000/predict -H "Content-Type: application/json" -d @test_data.json
```

### 4. Konfigurasi Laravel

Tambahkan di file `.env` Laravel:
```env
ML_SERVICE_URL=http://localhost:5000
```

### 5. Selesai! ✅

Dashboard monitoring Laravel akan otomatis menggunakan hasil ML Anda.

## Troubleshooting

### Error: Module not found
```bash
pip install -r requirements.txt
```

### Error: Model not found
- Pastikan semua file model ada di folder `models/`
- Cek nama file sesuai dengan yang ada di `app.py`

### Port 5000 sudah digunakan
Edit `app.py` baris terakhir:
```python
app.run(host='0.0.0.0', port=5001, debug=True)  # Ganti port
```
Dan update `.env` Laravel:
```env
ML_SERVICE_URL=http://localhost:5001
```

### Service tidak bisa diakses dari Laravel
- Pastikan service berjalan
- Cek firewall
- Test dengan: `curl http://localhost:5000/health`

