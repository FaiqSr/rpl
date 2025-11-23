@echo off
echo ========================================
echo Starting ML Service
echo ========================================
echo.

cd /d "%~dp0"

echo Checking Python installation...
python --version
if errorlevel 1 (
    echo ERROR: Python tidak ditemukan!
    echo Silakan install Python terlebih dahulu.
    pause
    exit /b 1
)

echo.
echo Checking dependencies...
python -c "import flask; import tensorflow; import sklearn; print('All dependencies OK!')" 2>nul
if errorlevel 1 (
    echo.
    echo Installing dependencies...
    pip install -r requirements.txt
    if errorlevel 1 (
        echo ERROR: Gagal install dependencies!
        pause
        exit /b 1
    )
)

echo.
echo ========================================
echo Starting Flask ML Service...
echo ========================================
echo Service akan berjalan di: http://localhost:5000
echo Tekan Ctrl+C untuk stop
echo.

python app.py

pause

