@echo off
chcp 65001 >nul
cd /d "%~dp0"
echo ======================================================
echo  SETUP LAN DAU - PRICE TRACKER 2 SAN CELLphoneS + FPT
echo ======================================================
echo.
echo [1/5] Cai thu vien Laravel bang Composer...
composer install
if errorlevel 1 (
  echo.
  echo LOI: composer install chua thanh cong. Hay kiem tra Composer/PHP.
  pause
  exit /b 1
)

echo.
echo [2/5] Tao file .env neu chua co...
if not exist .env copy .env.example .env

echo.
echo [3/5] Tao APP_KEY...
php artisan key:generate

echo.
echo [4/5] Import database phone_price_tracker...
C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -P 3306 < database.sql
if errorlevel 1 (
  echo.
  echo LOI: Khong import duoc database. MySQL phai dang chay tren port 3306.
  echo Neu XAMPP bao port 3306 dang duoc dung, nhung lenh mysql van xem duoc VERSION thi van OK.
  pause
  exit /b 1
)

echo.
echo [5/5] Clear cache Laravel...
php artisan optimize:clear
php artisan route:clear

echo.
echo DONE. Bay gio chay 04_START_ALL.bat de mo web + bot cao gia nen.
pause
