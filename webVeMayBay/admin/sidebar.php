<?php
// Sidebar quản trị: gắn lớp active dựa trên file hiện tại
$current = basename($_SERVER['PHP_SELF']);
function admin_active($file, $current) { return $file === $current ? 'active' : ''; }
?>
<div class="admin-sidebar">
  <div class="card shadow-sm mb-3">
    <div class="card-body py-3">
      <h2 class="h6 fw-semibold d-flex align-items-center gap-2 mb-3"><i class="bi bi-speedometer2 text-primary"></i> Bảng điều khiển</h2>
      <nav class="nav flex-column gap-1">
        <a class="nav-link <?php echo admin_active('index.php',$current); ?>" href="index.php"><i class="bi bi-house-door me-2"></i>Trang chủ</a>
        <a class="nav-link <?php echo admin_active('flights.php',$current); ?>" href="flights.php"><i class="bi bi-airplane-engines me-2"></i>Chuyến bay</a>
        <a class="nav-link <?php echo admin_active('bookings.php',$current); ?>" href="bookings.php"><i class="bi bi-journal-bookmark me-2"></i>Đặt chỗ & TT</a>
        <a class="nav-link <?php echo admin_active('users.php',$current); ?>" href="users.php"><i class="bi bi-people me-2"></i>Người dùng</a>
      </nav>
    </div>
  </div>
  <div class="small text-muted">© <?php echo date('Y'); ?> Quản trị</div>
</div>