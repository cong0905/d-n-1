<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

// Thu thập tham số (GET ưu tiên để chia sẻ link; POST fallback)
$origin = trim((string)post('origin', get('origin', '')));
$destination = trim((string)post('destination', get('destination', '')));
$date = trim((string)post('date', get('date', '')));
$pax = (int)(post('number_of_passengers', get('number_of_passengers', 1)));
$min_price = (float)post('min_price', get('min_price', ''));
$max_price = (float)post('max_price', get('max_price', ''));
$dep_from = trim((string)post('dep_from', get('dep_from', ''))); // HH:MM
$dep_to = trim((string)post('dep_to', get('dep_to', '')));
$min_seats = (int)post('min_seats', get('min_seats', ''));
$sort = strtolower((string)post('sort', get('sort', 'departure_time')));
$dir = strtolower((string)post('dir', get('dir', 'asc')));
$per_page = (int)post('per_page', get('per_page', 9));
$page = max(1, (int)get('page', post('page', 1)));

// Chuẩn hóa sort/dir
$allowedSort = ['departure_time','price'];
if (!in_array($sort, $allowedSort, true)) $sort = 'departure_time';
$dir = $dir === 'desc' ? 'DESC' : 'ASC';
$per_page = in_array($per_page, [9,12,18], true) ? $per_page : 9;

$errors = [];
$flights = [];
$total = 0;
$filterBadges = [];

// Hàm escape wildcard cho LIKE
function like_escape($str) {
    return str_replace(['%','_'], ['\\%','\\_'], $str);
}

// Xây dựng câu truy vấn động
$conditions = [];
$params = [];

if ($origin !== '') {
  $conditions[] = 'origin LIKE ?';
  $params[] = '%' . like_escape($origin) . '%';
  $filterBadges[] = 'Origin: ' . escape($origin);
}
if ($destination !== '') {
  $conditions[] = 'destination LIKE ?';
  $params[] = '%' . like_escape($destination) . '%';
  $filterBadges[] = 'Destination: ' . escape($destination);
}
if ($date !== '') {
  $conditions[] = 'DATE(departure_time) = ?';
  $params[] = $date;
  $filterBadges[] = 'Ngày: ' . escape($date);
}
if ($min_price > 0) {
  $conditions[] = 'price >= ?';
  $params[] = $min_price;
  $filterBadges[] = 'Giá ≥ ' . number_format($min_price,0,',','.');
}
if ($max_price > 0 && ($max_price >= $min_price || $min_price === 0)) {
  $conditions[] = 'price <= ?';
  $params[] = $max_price;
  $filterBadges[] = 'Giá ≤ ' . number_format($max_price,0,',','.');
}
if ($dep_from !== '') {
  $conditions[] = 'TIME(departure_time) >= ?';
  $params[] = $dep_from;
  $filterBadges[] = 'KH từ ' . escape($dep_from);
}
if ($dep_to !== '') {
  $conditions[] = 'TIME(departure_time) <= ?';
  $params[] = $dep_to;
  $filterBadges[] = 'KH đến ' . escape($dep_to);
}
if ($min_seats > 0) {
  $conditions[] = 'seats_available >= ?';
  $params[] = $min_seats;
  $filterBadges[] = 'Ghế ≥ ' . (int)$min_seats;
}

// Nếu không có điều kiện nào => lấy tất cả (tùy yêu cầu)
$where = '';
if ($conditions) {
  $where = 'WHERE ' . implode(' AND ', $conditions);
}

try {
  // Đếm tổng
  $countSql = "SELECT COUNT(*) FROM flights $where";
  $stmtCount = $pdo->prepare($countSql);
  $stmtCount->execute($params);
  $total = (int)$stmtCount->fetchColumn();

  // Phân trang
  $offset = ($page - 1) * $per_page;
  $listSql = "SELECT * FROM flights $where ORDER BY $sort $dir LIMIT $per_page OFFSET $offset";
  $stmtList = $pdo->prepare($listSql);
  $stmtList->execute($params);
  $flights = $stmtList->fetchAll();
} catch (Throwable $e) {
  app_log_error('SEARCH_ERROR', ['error' => $e->getMessage()]);
  $errors[] = 'Có lỗi khi tìm chuyến. Vui lòng thử lại sau.';
}

$totalPages = max(1, (int)ceil($total / $per_page));
?>

<h1 class="h4 mb-4 d-flex align-items-center gap-2"><i class="bi bi-list-ul text-primary"></i> Kết quả tìm kiếm</h1>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <span class="badge bg-light text-dark border"><i class="bi bi-collection me-1"></i>Tổng: <?php echo $total; ?></span>
  <?php foreach ($filterBadges as $b): ?>
    <span class="badge filter-badge"><?php echo $b; ?></span>
  <?php endforeach; ?>
  <?php if (!$filterBadges): ?><span class="small text-muted">Không áp dụng bộ lọc nào.</span><?php endif; ?>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger alert-modern mb-4"><?php echo escape(implode('\n', $errors)); ?></div>
<?php endif; ?>

<?php if ($flights): ?>
  <div class="row g-4">
    <?php foreach ($flights as $f): ?>
      <?php $disabled = ($pax > (int)$f['seats_available']); ?>
      <div class="col-sm-6 col-lg-4">
        <div class="card flight-card h-100 card-lift reveal-on-scroll">
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1"><i class="bi bi-airplane-engines me-1"></i><?php echo escape($f['flight_number']); ?></span>
              <span class="badge bg-light text-dark border"><i class="bi bi-person-seat me-1"></i><?php echo escape($f['seats_available']); ?> ghế</span>
            </div>
            <h2 class="h6 fw-semibold mb-1 route-text"><i class="bi bi-geo-alt me-1 text-primary"></i><?php echo escape($f['origin']); ?> <span class="text-muted">→</span> <?php echo escape($f['destination']); ?></h2>
            <div class="small text-muted mb-2">
              <i class="bi bi-clock me-1"></i>KH: <?php echo escape($f['departure_time']); ?><br>
              <i class="bi bi-clock-history me-1"></i>ĐẾN: <?php echo escape($f['arrival_time']); ?>
            </div>
            <div class="mt-auto d-flex justify-content-between align-items-center pt-2">
              <div class="price fw-bold text-primary"><i class="bi bi-cash-coin me-1"></i><?php echo number_format((float)$f['price'], 0, ',', '.'); ?> VND</div>
              <a class="btn btn-sm btn-success <?php echo $disabled ? 'disabled' : ''; ?>" href="<?php echo $disabled ? '#' : ('book.php?flight_id=' . urlencode($f['id']) . '&pax=' . urlencode($pax)); ?>" <?php echo $disabled ? 'aria-disabled="true"' : ''; ?>><?php echo $disabled ? 'Hết ghế' : 'Đặt vé'; ?></a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if ($totalPages > 1): ?>
    <nav class="mt-4" aria-label="Phân trang">
      <ul class="pagination pagination-sm mb-0">
        <?php
        // Tạo query base (giữ filters, thay page)
        $qs = $_GET;
        for ($p = 1; $p <= $totalPages; $p++):
          $qs['page'] = $p;
          $url = 'search.php?' . http_build_query($qs);
        ?>
          <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>"><a class="page-link" href="<?php echo escape($url); ?>"><?php echo $p; ?></a></li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
<?php else: ?>
  <div class="card border-0 bg-white shadow-sm p-4 text-center">
    <p class="mb-0 text-muted"><i class="bi bi-search me-1"></i>Không tìm thấy chuyến phù hợp.</p>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
