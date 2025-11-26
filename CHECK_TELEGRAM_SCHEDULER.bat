@echo off
echo ========================================
echo  Check Telegram Scheduler Status
echo ========================================
echo.

cd /d "%~dp0rpl"

echo Checking scheduled tasks...
php artisan schedule:list

echo.
echo ========================================
echo  Recent Telegram Logs
echo ========================================
echo.

if exist storage\logs\laravel.log (
    echo Last 20 Telegram-related log entries:
    type storage\logs\laravel.log | findstr /i "Telegram telegram" | findstr /v "^$" | more
) else (
    echo Log file not found.
)

echo.
echo ========================================
echo  Scheduler Log (if exists)
echo ========================================
echo.

if exist storage\logs\telegram-scheduler.log (
    echo Last 20 scheduler log entries:
    type storage\logs\telegram-scheduler.log | more
) else (
    echo Scheduler log file not found.
    echo This is normal if scheduler hasn't run yet.
)

echo.
echo ========================================
echo  Manual Test
echo ========================================
echo.
echo To test manually, run:
echo   php artisan telegram:send-monitoring
echo.
pause

