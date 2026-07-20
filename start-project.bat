@echo off
title Supply Chain Risk - Project Starter

echo ============================================
echo   Supply Chain Risk Management
echo   Auto Starter Script
echo ============================================
echo.

cd /d "%~dp0"

echo [1/2] Starting Laravel Server...
start "Laravel Server" cmd /k "php artisan serve"
timeout /t 2 /nobreak >nul

echo [2/2] Starting Scheduler...
start "Laravel Scheduler" cmd /k "php artisan schedule:work"
timeout /t 2 /nobreak >nul

echo.
echo ============================================
echo   Project Started Successfully!
echo   - Laravel Server: http://127.0.0.1:8000
echo   - Scheduler: Running in background
echo ============================================
echo.
echo Close this window anytime (servers will keep running)
echo.
pause
