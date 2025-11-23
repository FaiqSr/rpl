@echo off
title ChickPatrol - Starting Website
color 0B

echo ========================================
echo   Starting ChickPatrol Website
echo ========================================
echo.

cd /d "%~dp0"

echo [1/3] Checking Laragon...
echo Pastikan Laragon (Apache + MySQL) sudah running!
echo.
pause

echo.
echo [2/3] Checking ML Service...
netstat -ano | findstr :5000 >nul
if errorlevel 1 (
    echo ⚠️  ML Service tidak running!
    echo.
    echo Starting ML Service...
    start "ML Service" /MIN cmd /c "cd /d %~dp0ml_service && python app.py"
    timeout /t 3 /nobreak >nul
    echo ML Service started in background.
) else (
    echo ✅ ML Service sudah running
)

echo.
echo [3/3] Opening Website...
echo.
echo ========================================
echo   Website akan dibuka di browser
echo ========================================
echo.
echo   URL Options:
echo   1. http://localhost/rpl
echo   2. http://rpl.test
echo   3. http://localhost:8000 (if using artisan serve)
echo.
echo   Press any key to open browser...
pause >nul

start http://localhost/rpl

echo.
echo ========================================
echo   Website Opened!
echo ========================================
echo.
echo   JANGAN TUTUP TERMINAL INI jika menggunakan
echo   php artisan serve
echo.
echo   Untuk stop: Tekan Ctrl+C
echo ========================================
echo.

REM Option: Uncomment untuk auto-start artisan serve
REM php artisan serve

pause

