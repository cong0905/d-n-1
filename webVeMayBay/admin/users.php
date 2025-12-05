<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../db.php';
if (!is_admin()) {
    set_flash('error', 'Bạn không có quyền truy cập trang quản trị.');
    redirect('../index.php');
}
require_once __DIR__ . '/../includes/header.php';

$errors = [];

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');
    try {
        if ($action === 'create') {
            $name = trim((string)post('name'));
            $email = trim((string)post('email'));
            $password = (string)post('password');
            $role = post('role', 'user');
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
                throw new Exception('Dữ liệu không hợp lệ (tên/email/mật khẩu).');
            }
            if (!in_array($role, ['user','admin'], true)) $role = 'user';
            // unique email
            $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $st->execute([$email]);
            if ($st->fetch()) throw new Exception('Email đã tồn tại.');
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $ins->execute([$name, $email, $hash, $role]);
            set_flash('success', 'Đã tạo người dùng.');
            redirect('users.php');
        } elseif ($action === 'update') {
            $id = (int)post('id');
            $name = trim((string)post('name'));
            $email = trim((string)post('email'));
            $password = (string)post('password'); // optional
            $role = post('role', 'user');
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Dữ liệu không hợp lệ (tên/email).');
            }
            if (!in_array($role, ['user','admin'], true)) $role = 'user';
            // unique email except self
            $st = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
            $st->execute([$email, $id]);
            if ($st->fetch()) throw new Exception('Email đã tồn tại.');
            if ($password !== '') {
                if (strlen($password) < 8) throw new Exception('Mật khẩu tối thiểu 8 ký tự.');
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $upd = $pdo->prepare('UPDATE users SET name=?, email=?, password=?, role=? WHERE id = ?');
                $upd->execute([$name, $email, $hash, $role, $id]);
            } else {
                $upd = $pdo->prepare('UPDATE users SET name=?, email=?, role=? WHERE id = ?');
                $upd->execute([$name, $email, $role, $id]);
            }
            // if editing self role to non-admin, keep session role until next login
            set_flash('success', 'Đã cập nhật người dùng.');
            redirect('users.php');
        } elseif ($action === 'delete') {
            $id = (int)post('id');
            if ($id === (int)($_SESSION['user_id'] ?? 0)) {
                throw new Exception('Không thể xóa tài khoản đang đăng nhập.');
            }
            $del = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $del->execute([$id]);
            set_flash('success', 'Đã xóa người dùng.');
            redirect('users.php');
        }
    } catch (Throwable $e) {
        app_log_error('ADMIN_USER_MUTATION_ERROR', ['error' => $e->getMessage()]);
        $errors[] = 'Không thể thực hiện thao tác: ' . escape($e->getMessage());
    }
}

// Fetch for edit
$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([$eid]);
    $edit = $st->fetch();
}

// List users
$users = [];
$st = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC');
$users = $st->fetchAll();
?>

<h1 class="h4 mb-3">Quản lý người dùng</h1>

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
    <h1 class="h4 mb-4 d-flex align-items-center gap-2"><i class="bi bi-people text-primary"></i> Quản lý người dùng</h1>
    <div class="card shadow-soft card-lift mb-4 reveal-on-scroll">
      <div class="card-header fw-semibold bg-white border-0 pt-3"><i class="bi bi-pencil-square me-1 text-primary"></i><?php echo $edit ? 'Sửa người dùng' : 'Thêm người dùng'; ?></div>
      <div class="card-body">
        <form method="post" class="row g-3" data-loading>
      <?php if ($edit): ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?php echo escape($edit['id']); ?>">
      <?php else: ?>
        <input type="hidden" name="action" value="create">
      <?php endif; ?>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Họ tên</label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-person"></i></span><input type="text" name="name" class="form-control" value="<?php echo escape($edit['name'] ?? ''); ?>" required></div>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Email</label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span><input type="email" name="email" class="form-control" value="<?php echo escape($edit['email'] ?? ''); ?>" required></div>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">Vai trò</label>
        <select name="role" class="form-select" required>
          <option value="user" <?php echo isset($edit['role']) && $edit['role']==='user'?'selected':''; ?>>user</option>
          <option value="admin" <?php echo isset($edit['role']) && $edit['role']==='admin'?'selected':''; ?>>admin</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold"><?php echo $edit ? 'Mật khẩu (để trống nếu giữ nguyên)' : 'Mật khẩu'; ?></label>
        <div class="input-group"><span class="input-group-text bg-white"><i class="bi bi-lock"></i></span><input type="password" name="password" class="form-control" <?php echo $edit ? '' : 'required'; ?>></div>
      </div>
      <div class="col-12">
        <button class="btn btn-primary px-4" type="submit"><span class="label"><i class="bi bi-save me-1"></i><?php echo $edit ? 'Cập nhật' : 'Thêm'; ?></span></button>
        <?php if ($edit): ?><a class="btn btn-secondary" href="users.php"><i class="bi bi-x-circle me-1"></i>Hủy</a><?php endif; ?>
      </div>
    </form>
      </div>
    </div>
    <div class="table-responsive reveal-on-scroll">
      <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>#</th>
        <th>Họ tên</th>
        <th>Email</th>
        <th>Vai trò</th>
        <th>Ngày tạo</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?php echo escape($u['id']); ?></td>
            <td><?php echo escape($u['name']); ?></td>
            <td><?php echo escape($u['email']); ?></td>
            <td><?php echo escape($u['role']); ?></td>
            <td><?php echo escape($u['created_at']); ?></td>
            <td class="table-actions">
              <a class="btn btn-sm btn-outline-primary" href="users.php?edit=<?php echo urlencode($u['id']); ?>"><i class="bi bi-pencil"></i></a>
              <?php if ((int)$u['id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
              <form method="post" style="display:inline" onsubmit="return confirm('Xóa người dùng này?');" data-loading>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo escape($u['id']); ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit"><span class="label"><i class="bi bi-trash"></i></span></button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
    </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
