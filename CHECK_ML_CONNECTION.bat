@echo off
title Check ML Service Connection
color 0B

echo ========================================
echo   Check ML Service Connection
echo ========================================
echo.

cd /d "%~dp0"

echo [1/3] Checking .env configuration...
if exist .env (
    findstr /C:"ML_SERVICE_URL" .env
    if errorlevel 1 (
        echo WARNING: ML_SERVICE_URL tidak ditemukan di .env!
    ) else (
        echo OK: ML_SERVICE_URL ditemukan di .env
    )
) else (
    echo ERROR: File .env tidak ditemukan!
)

echo.
echo [2/3] Checking if ML Service is running...
netstat -ano | findstr :5000 >nul
if errorlevel 1 (
    echo ❌ ML Service TIDAK berjalan di port 5000
    echo.
    echo SOLUSI:
    echo 1. Jalankan START_ML_SERVICE.bat untuk start ML service
    echo 2. Atau jalankan: cd ml_service ^&^& python app.py
) else (
    echo ✅ Port 5000 sedang digunakan (ML Service mungkin berjalan)
)

echo.
echo [3/3] Testing connection...
cd ml_service
php test_connection.php
cd ..

echo.
echo ========================================
echo   Test Selesai!
echo ========================================
echo.
pause

