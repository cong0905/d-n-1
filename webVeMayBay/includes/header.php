<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0d6efd">
    <title>Hệ thống đặt vé máy bay</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- App CSS -->
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/style.css'); ?>">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-travel sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo base_url('index.php'); ?>">
      <i class="bi bi-airplane-fill brand-icon"></i>
      <span class="fw-bold">Vé Máy Bay</span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('index.php'); ?>"><i class="bi bi-search me-1"></i> Tìm chuyến</a></li>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo base_url('manage_booking.php'); ?>"><i class="bi bi-ticket-perforated me-1"></i> Đặt chỗ của tôi</a></li>
        <?php endif; ?>
        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo base_url('admin/index.php'); ?>"><i class="bi bi-speedometer2 me-1"></i> Quản trị</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <?php if (empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo base_url('login.php'); ?>"><i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập</a></li>
          <li class="nav-item ms-lg-2"><a class="btn btn-sm btn-primary px-3" href="<?php echo base_url('register.php'); ?>"><i class="bi bi-person-plus me-1"></i> Đăng ký</a></li>
        <?php else: ?>
          <li class="nav-item"><span class="navbar-text small text-nowrap"><i class="bi bi-person-circle me-1"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></span></li>
          <li class="nav-item ms-lg-3"><a class="btn btn-sm btn-outline-light px-3" href="<?php echo base_url('logout.php'); ?>"><i class="bi bi-box-arrow-right me-1"></i> Đăng xuất</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Page container -->
<main class="container py-4">
  <?php if (!empty($_SESSION['flash']['success'])): ?>
    <div class="alert alert-success alert-modern" role="alert">
      <i class="bi bi-check-circle me-1"></i>
      <?php echo htmlspecialchars($_SESSION['flash']['success']); unset($_SESSION['flash']['success']); ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash']['error'])): ?>
    <div class="alert alert-danger alert-modern" role="alert">
      <i class="bi bi-exclamation-triangle me-1"></i>
      <?php echo htmlspecialchars($_SESSION['flash']['error']); unset($_SESSION['flash']['error']); ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash']['info'])): ?>
    <div class="alert alert-info alert-modern" role="alert">
      <i class="bi bi-info-circle me-1"></i>
      <?php echo htmlspecialchars($_SESSION['flash']['info']); unset($_SESSION['flash']['info']); ?>
    </div>
  <?php endif; ?>
