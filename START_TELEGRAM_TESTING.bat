@echo off
echo ========================================
echo  Telegram Notification Testing Mode
echo  (Sends every 10 seconds)
echo ========================================
echo.
echo This will run Telegram notifications in TESTING MODE.
echo - Notifications will be sent every 10 seconds
echo - Press Ctrl+C to stop
echo.
cd /d "%~dp0rpl"
php artisan telegram:send-monitoring --test
pause

