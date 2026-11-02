@echo off
chcp 65001 >nul
cd /d "%~dp0"
echo Chay thu 1 vong cao 10 link de kiem tra co cap nhat database khong...
php artisan prices:run-cycle --limit=10
echo.
echo Xem lai web http://127.0.0.1:8000 hoac refresh trinh duyet.
pause
