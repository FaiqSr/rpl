@echo off
title ML Service - Monitoring Kandang Ayam
color 0A

echo ========================================
echo   ML Service - Monitoring Kandang Ayam
echo ========================================
echo.

cd /d "%~dp0"

echo [1/3] Checking Python...
python --version
if errorlevel 1 (
    echo ERROR: Python tidak ditemukan!
    echo Silakan install Python terlebih dahulu.
    pause
    exit /b 1
)

echo.
echo [2/3] Checking dependencies...
python -c "import flask, tensorflow, sklearn, numpy, joblib" 2>nul
if errorlevel 1 (
    echo Dependencies belum terinstall. Installing...
    pip install -r requirements.txt
    if errorlevel 1 (
        echo ERROR: Gagal install dependencies!
        pause
        exit /b 1
    )
)

echo.
echo [3/3] Starting ML Service...
echo.
echo ========================================
echo   Service akan berjalan di:
echo   http://localhost:5000
echo ========================================
echo.
echo   JANGAN TUTUP WINDOW INI!
echo   Service harus tetap running.
echo.
echo   Tekan Ctrl+C untuk stop service
echo ========================================
echo.

python app.py

pause

