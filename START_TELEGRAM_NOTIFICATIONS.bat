@echo off
echo ========================================
echo  Starting Laravel Scheduler
echo  (Telegram Notifications Every 5 Minutes)
echo ========================================
echo.
echo This will run the scheduler that sends Telegram notifications every 5 minutes.
echo.
echo IMPORTANT:
echo - Scheduler runs commands at: 00, 05, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55 minutes
echo - If you see "No scheduled commands are ready to run", wait until the next 5-minute mark
echo - Notifications only sent when condition is PERHATIAN or BURUK (not BAIK)
echo - DO NOT CLOSE THIS WINDOW - scheduler must keep running
echo.
echo Press Ctrl+C to stop.
echo.
cd /d "%~dp0rpl"
php artisan schedule:work
pause

