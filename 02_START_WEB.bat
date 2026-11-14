@echo off
chcp 65001 >nul
cd /d "%~dp0"
echo Dang chay web Laravel tai http://127.0.0.1:8000
echo De tat server: bam Ctrl + C trong cua so nay.
php artisan serve
pause
