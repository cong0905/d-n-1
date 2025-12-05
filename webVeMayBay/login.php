<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/header.php';

$email = '';
$errors = [];
$next = get('next');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)post('email'));
    $password = (string)post('password');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                $stored = $user['password'];
                $isHash = (strpos($stored, '$2y$') === 0) || (strpos($stored, '$argon2') === 0);
                $ok = false;
                if ($isHash) {
                    $ok = password_verify($password, $stored);
                } else {
                    // Seeded plaintext support: rehash on first successful login
                    if (hash_equals($stored, $password)) {
                        $ok = true;
                        $newHash = password_hash($password, PASSWORD_BCRYPT);
                        $up = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                        $up->execute([$newHash, $user['id']]);
                    }
                }

                if ($ok) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $user['name'];

                    set_flash('success', 'Đăng nhập thành công.');
                    if (!empty($_POST['next'])) {
                        redirect($_POST['next']);
                    }
                    if (!empty($next)) {
                        redirect($next);
                    }
                    redirect('index.php');
                } else {
                    $errors[] = 'Sai email hoặc mật khẩu.';
                }
            } else {
                $errors[] = 'Sai email hoặc mật khẩu.';
            }
        } catch (Throwable $e) {
            app_log_error('LOGIN_ERROR', ['error' => $e->getMessage()]);
            $errors[] = 'Có lỗi khi đăng nhập. Vui lòng thử lại.';
        }
    }
}
?>

<div class="auth-wrapper">
  <div class="card auth-card shadow-soft card-lift mb-4">
    <div class="card-body">
      <h1 class="h4 mb-4 d-flex align-items-center gap-2"><i class="bi bi-box-arrow-in-right text-primary"></i> Đăng nhập</h1>

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
        <div>
          <label class="form-label small fw-semibold">Email</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control" value="<?php echo escape($email); ?>" required>
          </div>
        </div>
        <div>
          <label class="form-label small fw-semibold">Mật khẩu</label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" required>
          </div>
        </div>
        <input type="hidden" name="next" value="<?php echo escape($next); ?>">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pt-2">
          <button class="btn btn-primary px-4" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập</button>
          <div class="small text-muted">Chưa có tài khoản? <a href="register.php" class="text-primary text-decoration-none fw-semibold">Đăng ký ngay</a></div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
