<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/db.php';
require_login();
require_once __DIR__ . '/includes/header.php';

$flight_id = (int)get('flight_id', 0);
$pax = max(1, (int)get('pax', 1));
$errors = [];
$flight = null;

try {
    if ($flight_id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM flights WHERE id = ? LIMIT 1');
        $stmt->execute([$flight_id]);
        $flight = $stmt->fetch();
        if (!$flight) {
            $errors[] = 'Không tìm thấy chuyến bay.';
        }
    } else {
        $errors[] = 'Thiếu mã chuyến bay.';
    }
} catch (Throwable $e) {
    app_log_error('BOOK_FETCH_FLIGHT_ERROR', ['error' => $e->getMessage()]);
    $errors[] = 'Có lỗi xảy ra khi tải thông tin chuyến bay.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errors) {
    $passenger_name = trim((string)post('passenger_name'));
    $phone = trim((string)post('phone'));
    $document_id = trim((string)post('document_id'));
    $pax = max(1, (int)post('pax', 1));

    if ($passenger_name === '') {
        $errors[] = 'Vui lòng nhập tên hành khách.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            // Khóa dòng chuyến bay để tránh tranh chấp khi đặt chỗ
            $lock = $pdo->prepare('SELECT id, seats_available, price FROM flights WHERE id = ? FOR UPDATE');
            $lock->execute([$flight_id]);
            $row = $lock->fetch();
            if (!$row) {
              throw new Exception('Không tìm thấy chuyến bay khi đặt chỗ.');
            }
            if ((int)$row['seats_available'] < $pax) {
                throw new Exception('Số ghế không đủ.');
            }
            $upd = $pdo->prepare('UPDATE flights SET seats_available = seats_available - ? WHERE id = ?');
            $upd->execute([$pax, $flight_id]);

            $total = (float)$row['price'] * $pax;
            $ins = $pdo->prepare('INSERT INTO bookings (user_id, flight_id, num_passengers, passenger_name, phone, document_id, status, total_amount) VALUES (?, ?, ?, ?, ?, ?, "pending_payment", ?)');
            $ins->execute([$_SESSION['user_id'], $flight_id, $pax, $passenger_name, $phone, $document_id, $total]);
            $booking_id = (int)$pdo->lastInsertId();

            $pdo->commit();

            $_SESSION['last_booking_id'] = $booking_id;
            $_SESSION['last_booking_amount'] = $total;
            set_flash('success', 'Đã tạo đặt chỗ. Vui lòng thanh toán.');
            redirect('payment.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            app_log_error('BOOK_CREATE_ERROR', ['error' => $e->getMessage()]);
          // Thông báo thân thiện cho người dùng
          $errors[] = 'Không thể đặt chỗ. Vui lòng thử lại.';
        }
    }
}
?>

<h1 class="h4 mb-4 d-flex align-items-center gap-2"><i class="bi bi-ticket-perforated text-primary"></i> Đặt vé</h1>

<?php if ($flight): ?>
  <div class="card mb-4 card-lift shadow-soft reveal-on-scroll">
    <div class="card-body">
      <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1"><i class="bi bi-airplane-engines me-1"></i><?php echo escape($flight['flight_number']); ?></span>
            <span class="badge bg-light text-dark border"><i class="bi bi-person-seat me-1"></i><?php echo escape($flight['seats_available']); ?> ghế còn</span>
          </div>
          <h2 class="h6 fw-semibold mb-2"><i class="bi bi-geo-alt me-1 text-primary"></i><?php echo escape($flight['origin']); ?> <span class="text-muted">→</span> <?php echo escape($flight['destination']); ?></h2>
          <div class="small text-muted mb-1"><i class="bi bi-clock me-1"></i>Khởi hành: <?php echo escape($flight['departure_time']); ?></div>
          <div class="small text-muted mb-2"><i class="bi bi-clock-history me-1"></i>Đến: <?php echo escape($flight['arrival_time']); ?></div>
          <div class="fw-semibold text-primary"><i class="bi bi-cash-coin me-1"></i>Giá: <?php echo number_format((float)$flight['price'], 0, ',', '.'); ?> VND</div>
        </div>
        <div class="text-md-end">
          <div class="small text-muted">Số hành khách</div>
          <div class="display-6 fw-bold mb-2" style="font-size:2.2rem; line-height:1;"><?php echo escape($pax); ?></div>
          <div class="badge bg-info-subtle text-info px-3 py-2"><i class="bi bi-info-circle me-1"></i>Kiểm tra lại trước khi đặt</div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="alert alert-danger alert-modern mb-4">
    <ul class="mb-0 small">
      <?php foreach ($errors as $er): ?>
        <li><?php echo escape($er); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($flight && !$errors): ?>
  <div class="card shadow-sm auth-card mb-4 reveal-on-scroll">
    <div class="card-body">
      <h2 class="h6 fw-semibold mb-3 d-flex align-items-center gap-2"><i class="bi bi-person-badge text-primary"></i> Thông tin hành khách</h2>
      <form method="post" class="row g-3" data-loading>
        <input type="hidden" name="pax" value="<?php echo escape($pax); ?>">
        <div class="col-md-6">
          <label class="form-label small fw-semibold">Tên hành khách</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
            <input type="text" name="passenger_name" class="form-control" required>
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Số điện thoại</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
            <input type="text" name="phone" class="form-control">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">CMND/Hộ chiếu</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-file-earmark-person"></i></span>
            <input type="text" name="document_id" class="form-control">
          </div>
        </div>
        <div class="col-12 pt-2">
          <button class="btn btn-success px-4" type="submit"><span class="label"><i class="bi bi-check-circle me-1"></i> Xác nhận đặt</span></button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
