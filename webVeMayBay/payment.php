<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/db.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$booking = null;
$errors = [];
$booking_id = isset($_SESSION['last_booking_id']) ? (int)$_SESSION['last_booking_id'] : 0;

try {
    if ($booking_id > 0) {
        $stmt = $pdo->prepare('SELECT b.*, f.flight_number, f.origin, f.destination, f.departure_time, f.arrival_time FROM bookings b JOIN flights f ON b.flight_id = f.id WHERE b.id = ? AND b.user_id = ? LIMIT 1');
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $stmt->fetch();
    }
    if (!$booking) {
        $errors[] = 'Không tìm thấy đặt chỗ để thanh toán.';
    }
} catch (Throwable $e) {
    app_log_error('PAYMENT_LOAD_BOOKING_ERROR', ['error' => $e->getMessage()]);
    $errors[] = 'Có lỗi khi tải thông tin đặt chỗ.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errors) {
    $method = 'mock'; // For real integration, replace with PayPal/Stripe SDK
    try {
        $pdo->beginTransaction();
        // Confirm current status
        $check = $pdo->prepare('SELECT status, total_amount FROM bookings WHERE id = ? AND user_id = ? FOR UPDATE');
        $check->execute([$booking_id, $_SESSION['user_id']]);
        $cur = $check->fetch();
        if (!$cur) throw new Exception('Không tìm thấy đặt chỗ.');
        if ($cur['status'] === 'confirmed') {
            // Already paid
        } else {
            $ins = $pdo->prepare('INSERT INTO payments (booking_id, amount, method, status, transaction_ref) VALUES (?, ?, ?, "paid", ?)');
            $ins->execute([$booking_id, $cur['total_amount'], $method, 'MOCK-' . time()]);
            $up = $pdo->prepare('UPDATE bookings SET status = "confirmed" WHERE id = ?');
            $up->execute([$booking_id]);
        }
        $pdo->commit();
        set_flash('success', 'Thanh toán thành công! Đặt chỗ đã được xác nhận.');
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        app_log_error('PAYMENT_PROCESS_ERROR', ['error' => $e->getMessage()]);
        $errors[] = 'Thanh toán thất bại. Vui lòng thử lại.';
    }
}
?>

<h1 class="h4 mb-4 d-flex align-items-center gap-2"><i class="bi bi-credit-card text-primary"></i> Thanh toán</h1>

<?php if ($errors): ?>
  <div class="alert alert-danger alert-modern mb-4">
    <ul class="mb-0 small">
      <?php foreach ($errors as $er): ?>
        <li><?php echo escape($er); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($booking): ?>
  <div class="card mb-4 card-lift shadow-soft reveal-on-scroll">
    <div class="card-body">
      <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1"><i class="bi bi-hash me-1"></i>#<?php echo escape($booking['id']); ?></span>
            <?php $st = $booking['status']; $cls = $st==='confirmed' ? 'status-confirmed' : ($st==='cancelled'?'status-cancelled':'status-pending'); ?>
            <span class="badge status-badge <?php echo $cls; ?> px-2 py-1"><?php echo escape($booking['status']); ?></span>
          </div>
          <h2 class="h6 fw-semibold mb-2"><i class="bi bi-airplane-engines me-1 text-primary"></i><?php echo escape($booking['flight_number']); ?>: <?php echo escape($booking['origin']); ?> <span class="text-muted">→</span> <?php echo escape($booking['destination']); ?></h2>
          <div class="small text-muted mb-1"><i class="bi bi-clock me-1"></i>Khởi hành: <?php echo escape($booking['departure_time']); ?></div>
          <div class="small text-muted mb-2"><i class="bi bi-clock-history me-1"></i>Đến: <?php echo escape($booking['arrival_time']); ?></div>
          <div class="fw-semibold text-primary"><i class="bi bi-people me-1"></i>Hành khách: <?php echo escape($booking['num_passengers']); ?></div>
        </div>
        <div class="text-md-end">
          <div class="small text-muted">Tổng tiền</div>
          <div class="display-6 fw-bold text-primary" style="font-size:2.2rem; line-height:1;"><?php echo number_format((float)$booking['total_amount'], 0, ',', '.'); ?></div>
          <div class="small text-muted">VND</div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($booking['status'] !== 'confirmed'): ?>
    <div class="card shadow-sm auth-card mb-4 reveal-on-scroll">
      <div class="card-body">
        <h2 class="h6 fw-semibold mb-3 d-flex align-items-center gap-2"><i class="bi bi-wallet2 text-primary"></i> Chọn phương thức thanh toán</h2>
        <form method="post" class="vstack gap-3" data-loading>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="method" value="mock" id="payMock" checked>
            <label class="form-check-label" for="payMock">
              <i class="bi bi-lightning-charge text-warning me-1"></i>Thanh toán giả lập (thành công ngay)
            </label>
          </div>
          <div class="alert alert-info small mb-0"><i class="bi bi-info-circle me-1"></i>Tích hợp cổng thực (PayPal/Stripe) có thể thêm sau.</div>
          <div>
            <button class="btn btn-success px-4" type="submit"><span class="label"><i class="bi bi-credit-card-2-back me-1"></i> Thanh toán</span></button>
          </div>
        </form>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-success alert-modern"><i class="bi bi-check-circle me-1"></i>Đặt chỗ đã được xác nhận. Cảm ơn bạn!</div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
