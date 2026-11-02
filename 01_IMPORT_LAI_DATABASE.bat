@echo off
chcp 65001 >nul
cd /d "%~dp0"
echo Dang import lai database phone_price_tracker...
C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -P 3306 < database.sql
if errorlevel 1 (
  echo LOI: MySQL chua ket noi duoc. Kiem tra MySQL port 3306.
  pause
  exit /b 1
)
php artisan optimize:clear
php artisan route:clear
echo DONE. Da import lai database.
pause
