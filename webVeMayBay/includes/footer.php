  </main>

  <!-- Footer -->
  <footer class="footer-travel mt-auto pt-5 pb-4">
    <div class="container">
      <div class="row g-4 align-items-start">
        <div class="col-12 col-md-4">
          <a class="d-flex align-items-center gap-2 mb-2 text-decoration-none" href="<?php echo base_url('index.php'); ?>">
            <i class="bi bi-airplane-fill brand-icon"></i>
            <span class="fw-bold text-white">Vé Máy Bay</span>
          </a>
          <p class="text-white-50 small mb-0">Đặt vé nhanh chóng, giá tốt, trải nghiệm mượt mà cho mọi hành trình.</p>
        </div>
        <div class="col-6 col-md-4">
          <h6 class="text-white-50 text-uppercase small mb-3">Liên kết</h6>
          <ul class="list-unstyled footer-links">
            <li><a href="<?php echo base_url('index.php'); ?>"><i class="bi bi-chevron-right"></i> Tìm chuyến bay</a></li>
            <li><a href="<?php echo base_url('manage_booking.php'); ?>"><i class="bi bi-chevron-right"></i> Đặt chỗ của tôi</a></li>
            <li><a href="<?php echo base_url('login.php'); ?>"><i class="bi bi-chevron-right"></i> Đăng nhập</a></li>
            <li><a href="<?php echo base_url('register.php'); ?>"><i class="bi bi-chevron-right"></i> Đăng ký</a></li>
          </ul>
        </div>
        <div class="col-6 col-md-4">
          <h6 class="text-white-50 text-uppercase small mb-3">Hỗ trợ</h6>
          <ul class="list-unstyled footer-links">
            <li><i class="bi bi-telephone me-2"></i> 66773508</li>
            <li><i class="bi bi-envelope me-2"></i> not_support@example.com</li>
            <li><i class="bi bi-clock me-2"></i> 08:00 - 22:00 (T2-CN)</li>
          </ul>
        </div>
      </div>
      <hr class="border-light border-opacity-10 my-4">
      <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
        <p class="small text-white-50 mb-0">© <?php echo date('Y'); ?> Vé Máy Bay. All rights reserved.</p>
        <div class="d-flex gap-3">
          <a class="social-link" href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a class="social-link" href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a class="social-link" href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <script src="<?php echo asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
