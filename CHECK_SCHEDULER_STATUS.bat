@echo off
echo ========================================
echo  Checking Laravel Scheduler Status
echo ========================================
echo.
cd /d "%~dp0rpl"
echo Checking scheduled tasks...
php artisan schedule:list
echo.
echo ========================================
echo  To start scheduler, run:
echo  START_TELEGRAM_NOTIFICATIONS.bat
echo ========================================
pause

