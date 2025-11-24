@echo off
echo ========================================
echo  Starting Laravel Scheduler
echo  (Telegram Notifications Every 5 Minutes)
echo ========================================
echo.
echo This will run the scheduler that sends Telegram notifications every 5 minutes.
echo Press Ctrl+C to stop.
echo.
cd /d "%~dp0rpl"
php artisan schedule:work
pause

