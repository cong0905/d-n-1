<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/db.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$errors = [];

// Handle cancel action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = (int)$_POST['cancel_id'];
    try {
        $pdo->beginTransaction();
        $sel = $pdo->prepare('SELECT id, user_id, flight_id, num_passengers, status FROM bookings WHERE id = ? FOR UPDATE');
        $sel->execute([$cancelId]);
        $bk = $sel->fetch();
        if (!$bk || (int)$bk['user_id'] !== (int)$_SESSION['user_id']) {
            throw new Exception('Không hợp lệ.');
        }
        if ($bk['status'] === 'cancelled') {
            // Already cancelled
        } else {
            $upb = $pdo->prepare('UPDATE bookings SET status = "cancelled" WHERE id = ?');
            $upb->execute([$cancelId]);
            // restore seats
            $upf = $pdo->prepare('UPDATE flights SET seats_available = seats_available + ? WHERE id = ?');
            $upf->execute([(int)$bk['num_passengers'], (int)$bk['flight_id']]);
        }
        $pdo->commit();
        set_flash('success', 'Đã hủy đặt chỗ.');
        redirect('manage_booking.php');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        app_log_error('BOOKING_CANCEL_ERROR', ['error' => $e->getMessage()]);
        $errors[] = 'Không thể hủy đặt chỗ.';
    }
}

// Load user's bookings
$rows = [];
try {
    $q = $pdo->prepare('SELECT b.*, f.flight_number, f.origin, f.destination, f.departure_time, f.arrival_time FROM bookings b JOIN flights f ON b.flight_id = f.id WHERE b.user_id = ? ORDER BY b.booking_date DESC');
    $q->execute([$_SESSION['user_id']]);
    $rows = $q->fetchAll();
} catch (Throwable $e) {
    app_log_error('BOOKING_LIST_ERROR', ['error' => $e->getMessage()]);
    $errors[] = 'Không thể tải danh sách đặt chỗ.';
}
?>

<h1 class="h4 mb-4 d-flex align-items-center gap-2"><i class="bi bi-journal-bookmark text-primary"></i> Đặt chỗ của tôi</h1>

<?php if ($errors): ?>
  <div class="alert alert-danger alert-modern mb-4">
    <ul class="mb-0 small">
      <?php foreach ($errors as $er): ?>
        <li><?php echo escape($er); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($rows): ?>
  <div class="row g-4">
    <?php foreach ($rows as $r): ?>
      <?php $st = $r['status']; $cls = $st==='confirmed' ? 'status-confirmed' : ($st==='cancelled'?'status-cancelled':'status-pending'); ?>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 card-lift shadow-soft reveal-on-scroll">
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="badge bg-primary-subtle text-primary px-2 py-1 fw-semibold"><i class="bi bi-hash me-1"></i><?php echo escape($r['id']); ?></span>
              <span class="badge status-badge <?php echo $cls; ?> px-2 py-1"><?php echo escape(status_label($r['status'])); ?></span>
            </div>
            <h2 class="h6 fw-semibold mb-1"><i class="bi bi-airplane-engines me-1 text-primary"></i><?php echo escape($r['flight_number']); ?> - <?php echo escape($r['origin']); ?> <span class="text-muted">→</span> <?php echo escape($r['destination']); ?></h2>
            <div class="small text-muted mb-2"><i class="bi bi-clock me-1"></i>Khởi hành: <?php echo escape($r['departure_time']); ?></div>
            <div class="d-flex justify-content-between align-items-end mt-auto pt-2">
              <div class="small">
                <div><i class="bi bi-people me-1"></i><?php echo escape($r['num_passengers']); ?> hành khách</div>
                <div class="fw-semibold text-primary"><i class="bi bi-cash-coin me-1"></i><?php echo number_format((float)$r['total_amount'], 0, ',', '.'); ?> VND</div>
              </div>
              <?php if ($r['status'] !== 'cancelled'): ?>
                <form method="post" onsubmit="return confirm('Bạn chắc muốn hủy đặt chỗ này?');" class="ms-2" data-loading>
                  <input type="hidden" name="cancel_id" value="<?php echo escape($r['id']); ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit"><span class="label"><i class="bi bi-x-circle me-1"></i>Hủy</span></button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="card p-4 text-center shadow-sm"><p class="mb-0 text-muted"><i class="bi bi-info-circle me-1"></i>Chưa có đặt chỗ nào.</p></div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
