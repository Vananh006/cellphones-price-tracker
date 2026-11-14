@echo off
chcp 65001 >nul
cd /d "%~dp0"
echo ======================================================
echo  BOT CAO GIA NEN LIEN TUC
echo  Moi vong: cao gia -> cap nhat store_prices + price_history -> gui email canh bao.
echo  Nghi 300 giay giua 2 vong. De tat bot: bam Ctrl + C.
echo ======================================================
:loop
php artisan prices:run-cycle --limit=100
timeout /t 300 /nobreak >nul
goto loop
