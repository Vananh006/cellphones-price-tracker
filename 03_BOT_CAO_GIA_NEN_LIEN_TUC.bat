@echo off
chcp 65001 >nul
cd /d "%~dp0"
echo ======================================================
echo  BOT 1 - CAO GIA NEN LIEN TUC
 echo Moi vong se cao mot so link, cap nhat store_prices va them price_history.
echo De tat bot: bam Ctrl + C trong cua so nay.
echo ======================================================
php artisan prices:crawl-loop --limit=100 --interval=300
pause
