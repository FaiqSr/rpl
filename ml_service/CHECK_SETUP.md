# ✅ Checklist Setup ML Service

## File yang Harus Ada

Pastikan file-file berikut ada di folder `ml_service/models/`:

- [x] `model_lstm_kandang.h5` ✅
- [x] `model_random_forest.pkl` ✅
- [x] `model_isolation_forest.pkl` ✅
- [x] `scaler_rf.pkl` ✅
- [x] `scaler_lstm.pkl` ✅
- [x] `scaler_if.pkl` ✅
- [x] `model_metadata.json` ✅

## Dependencies

Install dengan:
```bash
pip install -r requirements.txt
```

Dependencies yang diperlukan:
- [ ] Flask
- [ ] flask-cors
- [ ] tensorflow
- [ ] numpy
- [ ] scikit-learn
- [ ] joblib
- [ ] pandas

## Testing

### 1. Test Python & Dependencies
```bash
python -c "import flask, tensorflow, sklearn, numpy, joblib; print('OK')"
```

### 2. Test Load Models
```bash
python -c "from app import MODELS_LOADED; print('Models loaded:', MODELS_LOADED)"
```

### 3. Test Service
```bash
# Jalankan service
python app.py

# Di terminal lain, test health
curl http://localhost:5000/health
```

### 4. Test Full Pipeline
```bash
python test_service.py
```

## Konfigurasi Laravel

Pastikan di `.env` Laravel:
```env
ML_SERVICE_URL=http://localhost:5000
```

## Status

- [x] File model sudah ada
- [ ] Dependencies terinstall
- [ ] Service bisa dijalankan
- [ ] Health check berhasil
- [ ] Test prediction berhasil
- [ ] Laravel bisa connect

## Next Steps

1. ✅ File model sudah ada
2. ⏳ Install dependencies: `pip install -r requirements.txt`
3. ⏳ Jalankan service: `python app.py` atau `start_service.bat`
4. ⏳ Test service: `python test_service.py`
5. ⏳ Set `ML_SERVICE_URL` di Laravel `.env`
6. ⏳ Test dari Laravel dashboard

