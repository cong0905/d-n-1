<?php
// Kết nối CSDL bằng PDO
// Cần cập nhật các hằng số sau cho phù hợp môi trường XAMPP/MySQL của bạn
// - DB_HOST: thường là 'localhost' hoặc '127.0.0.1'
// - DB_PORT: mặc định 3306 (đổi nếu MySQL chạy cổng khác, ví dụ 3307)
// - DB_NAME: trùng với tên DB tạo bởi sql/seed.sql (mặc định: flight_db)
// - DB_USER: trên XAMPP thường là 'root'
// - DB_PASS: trên XAMPP thường rỗng '' (nếu bạn đã đặt mật khẩu cho root, hãy điền ở đây)

define('DB_HOST', '127.0.0.1'); // Thay đổi nếu cần
define('DB_PORT', 3307);        // Đổi nếu MySQL dùng cổng khác
define('DB_NAME', 'flight_db'); // CHANGE IF YOU RENAMED DB IN seed.sql
define('DB_USER', 'root');      // CHANGE FOR YOUR ENV
define('DB_PASS', '');          // CHANGE FOR YOUR ENV

$pdo = null;

try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (Throwable $e) {
    // Fallback logging without depending on includes/functions.php
    $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . DIRECTORY_SEPARATOR . 'app.log';
    @error_log(date('c') . ' [LOI KET NOI DB] ' . $e->getMessage() . PHP_EOL, 3, $logFile);
    // Friendly message for users
    http_response_code(500);
    echo 'Lỗi kết nối cơ sở dữ liệu. Vui lòng kiểm tra cấu hình trong file db.php.';
    exit;
}

?>
