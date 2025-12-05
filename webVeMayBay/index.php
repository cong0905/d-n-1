<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card shadow-soft card-lift mb-5 reveal-on-scroll">
  <div class="card-body p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
      <h1 class="h4 mb-0 d-flex align-items-center gap-2"><i class="bi bi-search text-primary"></i> Tìm chuyến bay</h1>
      <button type="button" class="btn btn-sm btn-outline-primary" id="toggleAdvanced"><i class="bi bi-sliders me-1"></i> Nâng cao</button>
    </div>
    <form method="get" action="search.php" class="row g-3" data-loading>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Điểm đi (Origin)</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
          <input type="text" name="origin" class="form-control" required placeholder="Hà Nội">
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Điểm đến (Destination)</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-geo"></i></span>
          <input type="text" name="destination" class="form-control" required placeholder="TP. Hồ Chí Minh">
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Ngày đi</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-calendar-event"></i></span>
          <input type="date" name="date" class="form-control" required>
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Số hành khách</label>
        <div class="input-group">
          <span class="input-group-text bg-white"><i class="bi bi-people"></i></span>
          <input type="number" name="number_of_passengers" class="form-control" min="1" value="1" required>
        </div>
      </div>
      <div class="col-12">
        <div id="advancedFilters" class="advanced-filters border rounded-xxl p-3 mt-2 d-none">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Giá từ (VND)</label>
              <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-cash"></i></span><input type="number" name="min_price" class="form-control" min="0" step="10000" placeholder="0"></div>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Giá đến (VND)</label>
              <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-cash-coin"></i></span><input type="number" name="max_price" class="form-control" min="0" step="10000" placeholder="3000000"></div>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Giờ KH từ</label>
              <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-clock"></i></span><input type="time" name="dep_from" class="form-control"></div>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Giờ KH đến</label>
              <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-clock-history"></i></span><input type="time" name="dep_to" class="form-control"></div>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Ghế trống tối thiểu</label>
              <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-person-seat"></i></span><input type="number" name="min_seats" class="form-control" min="1" placeholder="1"></div>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Sắp xếp theo</label>
              <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-sort-alpha-down"></i></span>
                <select name="sort" class="form-select">
                  <option value="departure_time">Thời gian KH</option>
                  <option value="price">Giá</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Thứ tự</label>
              <select name="dir" class="form-select">
                <option value="asc">Tăng dần</option>
                <option value="desc">Giảm dần</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold">Kết quả / trang</label>
              <select name="per_page" class="form-select">
                <option value="9">9</option>
                <option value="12">12</option>
                <option value="18">18</option>
              </select>
            </div>
          </div>
          <div class="small text-muted mt-3"><i class="bi bi-info-circle me-1"></i>Bạn có thể để trống các trường nâng cao nếu không cần lọc.</div>
        </div>
      </div>
      <div class="col-12 pt-2 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <button type="submit" class="btn btn-primary px-4"><span class="label"><i class="bi bi-search me-1"></i> Tìm chuyến</span></button>
        <a href="search.php" class="small text-muted text-decoration-none"><i class="bi bi-link-45deg"></i> Xem kết quả rỗng để duyệt tất cả</a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
