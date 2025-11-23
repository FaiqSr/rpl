@echo off
echo ========================================
echo   Checking ML Service Status
echo ========================================
echo.

echo [1/3] Checking if port 5000 is in use...
netstat -ano | findstr :5000
if errorlevel 1 (
    echo ❌ Port 5000 tidak digunakan - Service TIDAK running
    echo.
    echo Solusi: Jalankan START_ML_SERVICE.bat
    pause
    exit /b 1
) else (
    echo ✅ Port 5000 digunakan - Service mungkin running
)

echo.
echo [2/3] Testing health endpoint...
curl -s http://localhost:5000/health
if errorlevel 1 (
    echo ❌ Service tidak merespons
    echo.
    echo Solusi: Restart service dengan START_ML_SERVICE.bat
) else (
    echo.
    echo ✅ Service merespons dengan baik!
)

echo.
echo [3/3] Testing root endpoint...
curl -s http://localhost:5000/
if errorlevel 1 (
    echo ❌ Root endpoint tidak merespons
) else (
    echo ✅ Root endpoint OK
)

echo.
echo ========================================
echo   Status Check Selesai
echo ========================================
pause

