@echo off
echo ========================================
echo  Starting Sensor Data Scheduler
echo ========================================
echo.
echo This will automatically generate sensor data every hour.
echo Press Ctrl+C to stop.
echo.
cd /d "%~dp0"
php artisan schedule:work
pause

