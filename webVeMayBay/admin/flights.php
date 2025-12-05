<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../db.php';
if (!is_admin()) {
    set_flash('error', 'Bạn không có quyền truy cập trang quản trị.');
    redirect('../index.php');
}
require_once __DIR__ . '/../includes/header.php';

$errors = [];

// Create / Update / Delete handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');
    try {
        if ($action === 'create') {
            $flight_number = trim((string)post('flight_number'));
            $origin = trim((string)post('origin'));
            $destination = trim((string)post('destination'));
            $departure_time = trim((string)post('departure_time'));
            $arrival_time = trim((string)post('arrival_time'));
            $seats_total = max(1, (int)post('seats_total', 1));
            $seats_available = max(0, min($seats_total, (int)post('seats_available', $seats_total)));
            $price = (float)post('price', 0);

            if ($flight_number === '' || $origin === '' || $destination === '' || $departure_time === '' || $arrival_time === '') {
                throw new Exception('Vui lòng nhập đầy đủ thông tin.');
            }
            $ins = $pdo->prepare('INSERT INTO flights (flight_number, origin, destination, departure_time, arrival_time, seats_total, seats_available, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $ins->execute([$flight_number, $origin, $destination, $departure_time, $arrival_time, $seats_total, $seats_available, $price]);
            set_flash('success', 'Đã thêm chuyến bay.');
            redirect('flights.php');
        } elseif ($action === 'update') {
            $id = (int)post('id');
            $flight_number = trim((string)post('flight_number'));
            $origin = trim((string)post('origin'));
            $destination = trim((string)post('destination'));
            $departure_time = trim((string)post('departure_time'));
            $arrival_time = trim((string)post('arrival_time'));
            $seats_total = max(1, (int)post('seats_total', 1));
            $seats_available = max(0, min($seats_total, (int)post('seats_available', $seats_total)));
            $price = (float)post('price', 0);

            $upd = $pdo->prepare('UPDATE flights SET flight_number=?, origin=?, destination=?, departure_time=?, arrival_time=?, seats_total=?, seats_available=?, price=? WHERE id = ?');
            $upd->execute([$flight_number, $origin, $destination, $departure_time, $arrival_time, $seats_total, $seats_available, $price, $id]);
            set_flash('success', 'Đã cập nhật chuyến bay.');
            redirect('flights.php');
        } elseif ($action === 'delete') {
            $id = (int)post('id');
            $del = $pdo->prepare('DELETE FROM flights WHERE id = ?');
            $del->execute([$id]);
            set_flash('success', 'Đã xóa chuyến bay.');
            redirect('flights.php');
        }
    } catch (Throwable $e) {
        app_log_error('ADMIN_FLIGHT_MUTATION_ERROR', ['error' => $e->getMessage()]);
        $errors[] = 'Không thể thực hiện thao tác: ' . escape($e->getMessage());
    }
}

// Fetch for edit
$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $st = $pdo->prepare('SELECT * FROM flights WHERE id = ?');
    $st->execute([$eid]);
    $edit = $st->fetch();
}

// List flights
$flights = [];
$st = $pdo->query('SELECT * FROM flights ORDER BY departure_time DESC');
$flights = $st->fetchAll();
?>

<h1 class="h4 mb-3">Quản lý chuyến bay</h1>

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
    <h1 class="h4 mb-4 d-flex align-items-center gap-2"><i class="bi bi-airplane-engines text-primary"></i> Quản lý chuyến bay</h1>
    <div class="card shadow-soft card-lift mb-4 reveal-on-scroll">
      <div class="card-header fw-semibold bg-white border-0 pt-3">
        <i class="bi bi-pencil-square me-1 text-primary"></i><?php echo $edit ? 'Sửa chuyến bay' : 'Thêm chuyến bay'; ?>
      </div>
      <div class="card-body">
        <form method="post" class="row g-3" data-loading>
      <?php if ($edit): ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo escape($edit['id']); ?>">
      <?php else: ?>
        <input type="hidden" name="action" value="create">
      <?php endif; ?>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Mã chuyến</label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-hash"></i></span><input type="text" name="flight_number" class="form-control" value="<?php echo escape($edit['flight_number'] ?? ''); ?>" required></div>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Điểm đi</label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span><input type="text" name="origin" class="form-control" value="<?php echo escape($edit['origin'] ?? ''); ?>" required></div>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Điểm đến</label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-geo"></i></span><input type="text" name="destination" class="form-control" value="<?php echo escape($edit['destination'] ?? ''); ?>" required></div>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Giá (VND)</label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-cash-coin"></i></span><input type="number" step="1000" name="price" class="form-control" value="<?php echo escape($edit['price'] ?? '0'); ?>" required></div>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Khởi hành</label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-clock"></i></span><input type="datetime-local" name="departure_time" class="form-control" value="<?php echo isset($edit['departure_time']) ? escape(str_replace(' ', 'T', substr($edit['departure_time'], 0, 16))) : ''; ?>" required></div>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Đến</label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-clock-history"></i></span><input type="datetime-local" name="arrival_time" class="form-control" value="<?php echo isset($edit['arrival_time']) ? escape(str_replace(' ', 'T', substr($edit['arrival_time'], 0, 16))) : ''; ?>" required></div>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Tổng ghế</label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-person-seat"></i></span><input type="number" name="seats_total" class="form-control" min="1" value="<?php echo escape($edit['seats_total'] ?? '100'); ?>" required></div>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Ghế trống</label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-person-seat-fill"></i></span><input type="number" name="seats_available" class="form-control" min="0" value="<?php echo escape($edit['seats_available'] ?? '100'); ?>" required></div>
      </div>
      <div class="col-12">
        <button class="btn btn-primary px-4" type="submit"><span class="label"><i class="bi bi-save me-1"></i><?php echo $edit ? 'Cập nhật' : 'Thêm'; ?></span></button>
        <?php if ($edit): ?><a class="btn btn-secondary" href="flights.php"><i class="bi bi-x-circle me-1"></i>Hủy</a><?php endif; ?>
      </div>
    </form>
      </div>
    </div>
    <div class="table-responsive reveal-on-scroll">
      <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Mã</th>
        <th>Tuyến</th>
        <th>Khởi hành</th>
        <th>Đến</th>
        <th>Ghế trống/Tổng</th>
        <th>Giá</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
        <?php foreach ($flights as $f): ?>
          <tr>
            <td><?php echo escape($f['id']); ?></td>
            <td><?php echo escape($f['flight_number']); ?></td>
            <td><?php echo escape($f['origin'] . ' → ' . $f['destination']); ?></td>
            <td><?php echo escape($f['departure_time']); ?></td>
            <td><?php echo escape($f['arrival_time']); ?></td>
            <td><?php echo escape($f['seats_available'] . '/' . $f['seats_total']); ?></td>
            <td><?php echo number_format((float)$f['price'], 0, ',', '.'); ?> VND</td>
            <td class="table-actions">
              <a class="btn btn-sm btn-outline-primary" href="flights.php?edit=<?php echo urlencode($f['id']); ?>"><i class="bi bi-pencil"></i></a>
              <form method="post" style="display:inline" onsubmit="return confirm('Xóa chuyến bay này?');" data-loading>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo escape($f['id']); ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit"><span class="label"><i class="bi bi-trash"></i></span></button>
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
