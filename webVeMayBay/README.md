# Web Vé Máy Bay — PHP + MySQL (XAMPP)

Dự án web đặt vé máy bay đơn giản, dùng PHP thuần (PDO) và MySQL/MariaDB, chạy trên XAMPP. Phù hợp cho mục đích học tập và minh họa các thực hành an toàn cơ bản trong ứng dụng PHP.

## Tính năng chính

- Tìm kiếm chuyến bay theo điểm đi/đến và ngày bay.
- Đặt vé với kiểm tra số ghế còn lại, hoàn trả ghế khi hủy.
- Quản lý đặt chỗ của người dùng (xem, hủy).
- Khu vực Admin: quản lý chuyến bay, người dùng, và đặt chỗ.
- Phiên thanh toán giả lập, có sẵn điểm tích hợp cổng thanh toán thực.
- Ghi log lỗi cơ bản, bảo vệ chống XSS/SQLi, quản lý session an toàn.

## Yêu cầu hệ thống

- XAMPP (Apache + MySQL/MariaDB)
- PHP 8.x
- Windows (đã thử trên Windows; các hệ khác tương tự)

## Cấu trúc thư mục

```
webVeMayBay/
├── index.php
├── login.php
├── logout.php
├── register.php
├── search.php
├── book.php
├── payment.php
├── manage_booking.php
├── admin/
│   ├── index.php
│   ├── flights.php
│   ├── bookings.php
│   ├── users.php
│   └── sidebar.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── assets/
│   ├── css/style.css
│   └── js/main.js
├── sql/seed.sql
├── logs/
│   └── (log file sẽ được tạo khi chạy)
├── db.php
└── README.md
```

## Cài đặt nhanh (XAMPP trên Windows)

1) Sao chép mã nguồn vào `c:\xampp\htdocs\webVeMayBay`.

2) Tạo cơ sở dữ liệu và dữ liệu mẫu:
- Mở phpMyAdmin (`http://localhost/phpmyadmin`).
- Tạo database trùng tên với file `sql/seed.sql` (mặc định: `flight_db`).
- Import `sql/seed.sql`.

3) Cấu hình kết nối DB trong `db.php` (nếu cần):
- `DB_HOST` = `localhost`
- `DB_NAME` = `flight_db`
- `DB_USER` = `root`
- `DB_PASS` = `''` (mặc định XAMPP)

4) Khởi động Apache và MySQL bằng XAMPP Control Panel.

5) Truy cập: `http://localhost/webVeMayBay/`

## Tài khoản mẫu

- User: `user@gmail.com` / `Test1234`
- Admin: `admin@gmail.com` / `Admin1234`

Ghi chú: Mật khẩu seed ban đầu có thể là plaintext để thử nhanh. Ứng dụng sẽ chuyển sang bcrypt ở lần đăng nhập đầu (cơ chế nâng cấp tự động).

## Cách sử dụng nhanh

- Đăng ký hoặc dùng tài khoản mẫu để đăng nhập.
- Tìm chuyến tại `index.php` → xem kết quả ở `search.php`.
- Chọn chuyến phù hợp → đặt vé tại `book.php`.
- Thanh toán giả lập ở `payment.php` → đặt chỗ chuyển `confirmed`.
- Quản lý đặt chỗ tại `manage_booking.php` (có thể hủy, ghế được hoàn lại).
- Admin vào `admin/` để thêm/sửa/xóa chuyến bay, xem người dùng/đặt chỗ.

## Bảo mật đã áp dụng

- Prepared statements (PDO) cho mọi truy vấn.
- Băm mật khẩu bằng `password_hash(..., PASSWORD_BCRYPT)`.
- Escape output bằng helper `escape()` để giảm XSS.
- `session_regenerate_id(true)` khi đăng nhập để tránh session fixation.
- Transaction + `SELECT ... FOR UPDATE` khi trừ ghế/ghi đặt chỗ, giảm race condition.
- Validate input server-side cho các form quan trọng.
- Ghi log lỗi trong `logs/`. Ở production cần bật HTTPS, secure cookies, CSP/HSTS, ẩn thông tin lỗi.

## Tích hợp thanh toán thực (gợi ý)

- Tại `payment.php` có vị trí tích hợp PayPal/Stripe.
- Các bước mẫu: thêm SDK, tạo payment intent, xác nhận qua webhook, ghi `payments`, cập nhật `bookings.status = 'confirmed'`.

## Khắc phục sự cố

- Không ghi log: đảm bảo Apache có quyền ghi vào `logs/`.
- Lỗi kết nối DB: kiểm tra `db.php`, thông tin `DB_NAME`, và đã import `sql/seed.sql`.
- Trùng timezone: có thể set `date_default_timezone_set('Asia/Ho_Chi_Minh');` trong `includes/header.php`.

## Giấy phép

Mã nguồn dùng cho mục đích học tập. Không kèm giấy phép thương mại. Vui lòng không sử dụng dữ liệu/mật khẩu mẫu cho môi trường thật.
