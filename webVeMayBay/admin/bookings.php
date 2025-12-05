<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../db.php';
if (!is_admin()) {
    set_flash('error', 'Bạn không có quyền truy cập trang quản trị.');
    redirect('../index.php');
}
require_once __DIR__ . '/../includes/header.php';

$errors = [];

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $id = (int)$_POST['id'];
    $new = $_POST['status'];
    if (!in_array($new, ['pending_payment','confirmed','cancelled'], true)) {
        $errors[] = 'Trạng thái không hợp lệ';
    } else {
        try {
            $pdo->beginTransaction();
            $sel = $pdo->prepare('SELECT id, status, flight_id, num_passengers FROM bookings WHERE id = ? FOR UPDATE');
            $sel->execute([$id]);
            $bk = $sel->fetch();
            if ($bk) {
                if ($bk['status'] !== $new) {
                    $upd = $pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?');
                    $upd->execute([$new, $id]);
                    if ($new === 'cancelled' && $bk['status'] !== 'cancelled') {
                        $upf = $pdo->prepare('UPDATE flights SET seats_available = seats_available + ? WHERE id = ?');
                        $upf->execute([(int)$bk['num_passengers'], (int)$bk['flight_id']]);
                    }
                }
            }
            $pdo->commit();
            set_flash('success', 'Đã cập nhật trạng thái.');
            redirect('bookings.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            app_log_error('ADMIN_UPDATE_BOOKING_STATUS', ['error' => $e->getMessage()]);
            $errors[] = 'Không thể cập nhật trạng thái.';
        }
    }
}

// List all bookings and payments info
$rows = [];
try {
    $q = $pdo->query('SELECT b.*, u.name AS user_name, u.email, f.flight_number, f.origin, f.destination FROM bookings b JOIN users u ON b.user_id = u.id JOIN flights f ON b.flight_id = f.id ORDER BY b.booking_date DESC');
    $rows = $q->fetchAll();
} catch (Throwable $e) {
    app_log_error('ADMIN_LIST_BOOKINGS_ERROR', ['error' => $e->getMessage()]);
    $errors[] = 'Không thể tải danh sách đặt chỗ.';
}
?>

<h1 class="h4 mb-3">Quản lý đặt chỗ & thanh toán</h1>

<?php if ($errors): ?>
  <div class="alert alert-danger alert-modern mb-4">
    <ul class="mb-0 small">
      <?php foreach ($errors as $er): ?>
        <li><?php echo escape($er); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="admin-layout mb-4">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="flex-grow-1">
    <h1 class="h4 mb-4 d-flex align-items-center gap-2"><i class="bi bi-journal-bookmark text-primary"></i> Quản lý đặt chỗ & thanh toán</h1>
    <div class="table-responsive reveal-on-scroll">
      <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Khách</th>
        <th>Email</th>
        <th>Chuyến</th>
        <th>Hành khách</th>
        <th>Tổng tiền</th>
        <th>Trạng thái</th>
        <th>Cập nhật</th>
      </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $r): ?>
          <?php $st = $r['status']; $cls = $st==='confirmed' ? 'status-confirmed' : ($st==='cancelled'?'status-cancelled':'status-pending'); ?>
          <tr>
            <td><?php echo escape($r['id']); ?></td>
            <td><?php echo escape($r['user_name']); ?></td>
            <td><?php echo escape($r['email']); ?></td>
            <td><?php echo escape($r['flight_number'] . ' ' . $r['origin'] . '→' . $r['destination']); ?></td>
            <td><?php echo escape($r['num_passengers']); ?></td>
            <td><?php echo number_format((float)$r['total_amount'], 0, ',', '.'); ?> VND</td>
            <td><span class="badge status-badge <?php echo $cls; ?> px-2 py-1"><?php echo escape(status_label($r['status'])); ?></span></td>
            <td>
              <form method="post" class="d-flex gap-2 align-items-center" data-loading>
                <input type="hidden" name="id" value="<?php echo escape($r['id']); ?>">
                <select name="status" class="form-select form-select-sm" style="max-width: 180px;">
                  <option value="pending_payment" <?php echo $r['status']==='pending_payment'?'selected':''; ?>>chờ thanh toán</option>
                  <option value="confirmed" <?php echo $r['status']==='confirmed'?'selected':''; ?>>xác nhận</option>
                  <option value="cancelled" <?php echo $r['status']==='cancelled'?'selected':''; ?>>hủy</option>
                </select>
                <button class="btn btn-sm btn-primary" type="submit"><span class="label"><i class="bi bi-save"></i></span></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
    </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
