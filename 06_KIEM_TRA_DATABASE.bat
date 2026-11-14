@echo off
chcp 65001 >nul
cd /d "%~dp0"
C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -P 3306 -e "USE phone_price_tracker; SHOW TABLES; SELECT COUNT(*) AS total_links FROM store_prices; SELECT COUNT(*) AS da_co_gia FROM store_prices WHERE current_price > 0; SELECT COUNT(*) AS history_rows FROM price_history; SELECT MAX(updated_at) AS cap_nhat_gan_nhat FROM store_prices;"
pause
