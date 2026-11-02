@echo off
chcp 65001 >nul
cd /d "%~dp0"
echo Mo WEB + BOT CAO GIA NEN...
start "WEB Laravel 127.0.0.1:8000" cmd /k "cd /d "%~dp0" && php artisan serve"
timeout /t 2 /nobreak >nul
start "BOT cao gia nen lien tuc" cmd /k "cd /d "%~dp0" && php artisan prices:crawl-loop --limit=100 --interval=300"
timeout /t 2 /nobreak >nul
start "" "http://127.0.0.1:8000"
echo Da mo 2 cua so: WEB va BOT.
pause
