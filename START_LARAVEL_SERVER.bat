@echo off
title ChickPatrol - Laravel Server
color 0E

echo ========================================
echo   Starting Laravel Development Server
echo ========================================
echo.

cd /d "%~dp0"

echo [1/3] Checking PHP...
php --version
if errorlevel 1 (
    echo ERROR: PHP tidak ditemukan!
    echo Pastikan PHP terinstall dan ada di PATH.
    pause
    exit /b 1
)

echo.
echo [2/3] Checking Laravel...
if not exist "artisan" (
    echo ERROR: File artisan tidak ditemukan!
    echo Pastikan Anda di folder root Laravel.
    pause
    exit /b 1
)

echo.
echo [3/3] Starting Laravel Server...
echo.
echo ========================================
echo   Server akan berjalan di:
echo   http://localhost:8000
echo ========================================
echo.
echo   JANGAN TUTUP WINDOW INI!
echo   Server harus tetap running.
echo.
echo   Tekan Ctrl+C untuk stop server
echo ========================================
echo.

php artisan serve

pause

