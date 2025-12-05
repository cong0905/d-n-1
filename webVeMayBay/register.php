<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$name = '';
$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)post('name'));
    $email = trim((string)post('email'));
    $password = (string)post('password');
    $confirm = (string)post('confirm');

    if ($name === '') $errors[] = 'Tên không được để trống.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (strlen($password) < 8) $errors[] = 'Mật khẩu phải tối thiểu 8 ký tự.';
    if ($password !== $confirm) $errors[] = 'Xác nhận mật khẩu không khớp.';

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email đã được sử dụng.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $ins = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "user")');
                $ins->execute([$name, $email, $hash]);
                set_flash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
                redirect('login.php');
            }
        } catch (Throwable $e) {
            app_log_error('REGISTER_ERROR', ['error' => $e->getMessage()]);
            $errors[] = 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.';
        }
    }
}
?>

<div class="auth-wrapper">
  <div class="card auth-card shadow-soft card-lift mb-4">
    <div class="card-body">
      <h1 class="h4 mb-4 d-flex align-items-center gap-2"><i class="bi bi-person-plus text-primary"></i> Đăng ký</h1>

      <?php if ($errors): ?>
        <div class="alert alert-danger alert-modern mb-4">
          <ul class="mb-0 small">
            <?php foreach ($errors as $er): ?>
              <li><?php echo escape($er); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" class="vstack gap-3">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Họ tên</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
              <input type="text" name="name" class="form-control" value="<?php echo escape($name); ?>" required>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Email</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control" value="<?php echo escape($email); ?>" required>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Mật khẩu</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" class="form-control" required>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Xác nhận mật khẩu</label>
            <div class="input-group">
              <span class="input-group-text bg-white"><i class="bi bi-lock-fill"></i></span>
              <input type="password" name="confirm" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="small text-muted">
          <i class="bi bi-info-circle me-1"></i>Mật khẩu tối thiểu 8 ký tự. Nên gồm chữ hoa, chữ thường và số để tăng bảo mật.
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pt-2">
          <button class="btn btn-primary px-4" type="submit"><i class="bi bi-person-check me-1"></i> Tạo tài khoản</button>
          <div class="small text-muted">Đã có tài khoản? <a href="login.php" class="text-primary text-decoration-none fw-semibold">Đăng nhập</a></div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
