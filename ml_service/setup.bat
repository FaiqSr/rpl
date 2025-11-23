@echo off
echo ========================================
echo Setup ML Service untuk Monitoring
echo ========================================
echo.

echo [1/3] Membuat folder models...
if not exist "models" mkdir models
echo Folder models sudah dibuat.
echo.

echo [2/3] Install dependencies...
pip install -r requirements.txt
echo.

echo [3/3] Setup selesai!
echo.
echo ========================================
echo LANGKAH SELANJUTNYA:
echo ========================================
echo 1. Copy semua file model ke folder models/:
echo    - model_lstm_kandang.h5
echo    - model_random_forest.pkl
echo    - model_isolation_forest.pkl
echo    - scaler_rf.pkl
echo    - scaler_lstm.pkl
echo    - scaler_if.pkl
echo    - model_metadata.json
echo.
echo 2. Edit app.py jika SEQUENCE_LENGTH berbeda
echo.
echo 3. Jalankan service:
echo    python app.py
echo.
echo 4. Set di Laravel .env:
echo    ML_SERVICE_URL=http://localhost:5000
echo.
pause

