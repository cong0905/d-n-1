<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../db.php';
if (!is_admin()) {
    set_flash('error', 'Bạn không có quyền truy cập trang quản trị.');
    redirect('../index.php');
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="flex-grow-1">
    <h1 class="h4 mb-4 d-flex align-items-center gap-2"><i class="bi bi-speedometer2 text-primary"></i> Bảng điều khiển quản trị</h1>
    <div class="row g-4">
      <div class="col-md-4 reveal-on-scroll">
        <div class="card shadow-soft card-lift h-100">
          <div class="card-body d-flex flex-column">
            <h2 class="h6 fw-semibold d-flex align-items-center gap-2 mb-2"><i class="bi bi-airplane-engines text-primary"></i>Chuyến bay</h2>
            <p class="small text-muted-2 mb-3">Thêm, sửa, xoá và quản lý lịch trình chuyến bay.</p>
            <div class="mt-auto"><a href="flights.php" class="btn btn-sm btn-primary"><i class="bi bi-arrow-right-circle me-1"></i>Quản lý</a></div>
          </div>
        </div>
      </div>
      <div class="col-md-4 reveal-on-scroll">
        <div class="card shadow-soft card-lift h-100">
          <div class="card-body d-flex flex-column">
            <h2 class="h6 fw-semibold d-flex align-items-center gap-2 mb-2"><i class="bi bi-journal-bookmark text-primary"></i>Đặt chỗ & TT</h2>
            <p class="small text-muted-2 mb-3">Theo dõi trạng thái đặt chỗ, xử lý xác nhận và hủy.</p>
            <div class="mt-auto"><a href="bookings.php" class="btn btn-sm btn-primary"><i class="bi bi-arrow-right-circle me-1"></i>Quản lý</a></div>
          </div>
        </div>
      </div>
      <div class="col-md-4 reveal-on-scroll">
        <div class="card shadow-soft card-lift h-100">
          <div class="card-body d-flex flex-column">
            <h2 class="h6 fw-semibold d-flex align-items-center gap-2 mb-2"><i class="bi bi-people text-primary"></i>Người dùng</h2>
            <p class="small text-muted-2 mb-3">Quản lý tài khoản, vai trò và bảo mật người dùng.</p>
            <div class="mt-auto"><a href="users.php" class="btn btn-sm btn-primary"><i class="bi bi-arrow-right-circle me-1"></i>Quản lý</a></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
