@echo off
echo Starting ML Service in background...
cd /d "%~dp0"
start "ML Service" /MIN python app.py
timeout /t 3 /nobreak >nul
echo.
echo Service started! Check if it's running:
echo   http://localhost:5000/health
echo.
echo To stop: Close the "ML Service" window or find Python process
pause

