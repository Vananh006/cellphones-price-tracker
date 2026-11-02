# Hướng dẫn chạy bản FINAL - 2 bot CellphoneS + FPT Shop

## Bản này có gì?

Bản này dùng mô hình 2 bot đúng theo ý tưởng của bạn:

| Thành phần | Vai trò |
|---|---|
| Bot 1 - Cào nền liên tục | Chạy bằng `php artisan prices:crawl-loop --limit=8 --interval=60`, tự động cào từng nhóm link CellphoneS/FPT, cập nhật giá hiện tại vào `store_prices` và lưu lịch sử vào `price_history`. |
| Bot 2 - Cào live khi người dùng nhập link | Khi người dùng nhập link sản phẩm + email + giá mong muốn, web sẽ crawl ngay link đó, lưu vào database, tìm biến thể tương ứng, so sánh giá 2 sàn nếu database có đủ link tương ứng. |
| Database cũ | Dữ liệu đã import dùng làm nền để so sánh, lưu lịch sử và dự đoán giá tối ưu. |
| Dự đoán giá tối ưu | Dựa trên lịch sử giá trong `price_history` và dữ liệu hiện tại. Nếu lịch sử chưa đủ, hệ thống sẽ dùng giá tốt nhất hiện có làm tham chiếu. |
| Gửi mail | Logic tạo yêu cầu theo dõi đã có. Muốn gửi mail thật cần cấu hình SMTP trong `.env`. |

## Cách chạy nhanh nhất

### Bước 1: Mở MySQL

Bạn chỉ cần MySQL chạy được trên port `3306`. Không bắt buộc Apache chạy.

Nếu XAMPP báo đỏ kiểu `Port 3306 in use by Unable to open process`, nhưng lệnh dưới vẫn ra version MariaDB/MySQL thì MySQL vẫn đang chạy:

```bat
C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -P 3306 -e "SELECT VERSION();"
```

### Bước 2: Setup lần đầu

Bấm đúp file:

```text
00_SETUP_LAN_DAU.bat
```

File này sẽ chạy:

```bat
composer install
copy .env.example .env
php artisan key:generate
C:\xampp\mysql\bin\mysql.exe -u root -h 127.0.0.1 -P 3306 < database.sql
php artisan optimize:clear
php artisan route:clear
```

### Bước 3: Chạy web + bot nền

Bấm đúp file:

```text
04_START_ALL.bat
```

Nó sẽ mở 2 cửa sổ CMD:

1. WEB: `php artisan serve`
2. BOT nền: `php artisan prices:crawl-loop --limit=8 --interval=60`

Sau đó mở trình duyệt:

```text
http://127.0.0.1:8000
```

## Vì sao lúc đầu thấy “Đã có giá crawl = 0”?

Vì database ban đầu chủ yếu là danh sách link. Giá sẽ bắt đầu tăng dần khi bot nền chạy. Bạn có thể ép cào thử 1 vòng bằng:

```text
05_TEST_CRAWL_1_VONG.bat
```

Sau khi chạy xong, refresh web.

## Kiểm tra database có được cập nhật không

Bấm:

```text
06_KIEM_TRA_DATABASE.bat
```

Nếu `da_co_gia` hoặc `history_rows` tăng lên thì bot đã lưu giá vào database.

## Lưu ý quan trọng về độ chính xác giá

Crawler đã có bước kiểm tra chống giá sai quá thấp: hệ thống bỏ qua giá dưới 1.000.000đ và tránh lấy nhầm giá phụ kiện/trả góp. Tuy nhiên giá từ website thật có thể thay đổi giao diện hoặc tải bằng JavaScript, nên không có crawler nào cam kết chính xác 100% mãi mãi. Cách tối ưu trong đồ án là:

- lưu cả giá hiện tại và lịch sử giá;
- chỉ dùng giá vượt qua điều kiện kiểm tra hợp lý;
- khi giá crawl thất bại thì giữ link ở trạng thái chờ crawl lại;
- có thể kiểm tra thủ công các sản phẩm quan trọng trước khi demo/nộp.

## Cấu hình gửi mail thật

Mở file `.env` và sửa phần mail, ví dụ Gmail App Password:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="Price Tracker"
```

Sau đó chạy lại:

```bat
php artisan optimize:clear
```
