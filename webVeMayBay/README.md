# Web Vé Máy Bay

Website đặt vé máy bay cho mục đích học tập, sử dụng **PHP thuần, PDO và MySQL/MariaDB**. Giao diện và xử lý phía server nằm trong cùng ứng dụng; không cần Composer hoặc bước build frontend.

## Chức năng

- Đăng ký, đăng nhập và quản lý phiên người dùng.
- Tìm chuyến bay theo nơi đi, nơi đến và ngày bay.
- Đặt vé, kiểm tra ghế còn lại, xem và hủy đặt chỗ.
- Thanh toán **giả lập** để xác nhận booking.
- Khu vực admin quản lý chuyến bay, người dùng và đặt chỗ.

Các thao tác đặt/hủy vé có transaction và khóa bản ghi ở những đoạn xử lý tương ứng. Dự án không kết nối hệ thống hãng bay hay cổng thanh toán tiền thật.

## Chạy với XAMPP trên Windows

Cần Apache, PHP 8.x có `pdo_mysql`, và MySQL/MariaDB.

```powershell
git clone https://github.com/cong0905/d-n-1.git C:/xampp/htdocs/d-n-1
```

Sau khi clone, các đường dẫn bên dưới tính từ thư mục `webVeMayBay/` bên trong repository.

1. Bật **Apache** và **MySQL** trong XAMPP.
**Chỉ import seed trên database demo mới:** script có `DROP DATABASE IF EXISTS flight_db`, sẽ xóa database cùng tên. Sao lưu dữ liệu đang có trước khi sử dụng.

2. Import `sql/seed.sql` bằng phpMyAdmin; script tạo database `flight_db` và dữ liệu mẫu.
3. Sửa `db.php`: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` theo máy đang chạy. **Mã nguồn hiện đặt `DB_PORT=3307`**; đổi thành `3306` nếu MySQL của bạn dùng cổng mặc định.
4. Mở `http://localhost/d-n-1/webVeMayBay/`.

## Cấu trúc

| Đường dẫn | Nội dung |
| --- | --- |
| `index.php`, `search.php` | Tra cứu chuyến bay |
| `book.php`, `payment.php` | Đặt chỗ và xác nhận thanh toán giả lập |
| `manage_booking.php` | Booking của người dùng |
| `admin/` | Giao diện quản trị |
| `includes/`, `assets/` | Thành phần dùng chung, CSS và JavaScript |
| `sql/seed.sql` | Schema và dữ liệu mẫu |
| `db.php` | Kết nối PDO |

## Kiểm tra sau cài đặt

Đăng ký tài khoản → đăng nhập → tìm chuyến bay có trong seed → đặt vé → xác nhận thanh toán giả lập → xem/hủy booking. Kiểm tra cả trạng thái booking và số ghế còn lại trong database.

Nếu báo lỗi kết nối, đối chiếu cổng MySQL và thông tin trong `db.php`; lỗi được ghi trong `logs/app.log`. Không công khai log hoặc thông tin kết nối thật.

**Trạng thái:** bài thực hành PHP/MySQL; cần rà soát bảo mật, dữ liệu và tích hợp trước khi đưa vào sử dụng thực tế.

**Tác giả:** [Nguyễn Đức Công](https://github.com/cong0905).
