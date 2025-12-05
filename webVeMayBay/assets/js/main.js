// Placeholder for future enhancements
console.log("Ứng dụng đặt vé đã tải");

// Toggle bộ lọc nâng cao trên trang tìm kiếm
document.addEventListener("DOMContentLoaded", () => {
  const btn = document.getElementById("toggleAdvanced");
  const panel = document.getElementById("advancedFilters");
  if (btn && panel) {
    btn.addEventListener("click", () => {
      panel.classList.toggle("d-none");
      btn.classList.toggle("btn-outline-primary");
      btn.classList.toggle("btn-primary");
    });
  }
  // Reveal on scroll
  const reveals = document.querySelectorAll(".reveal-on-scroll");
  if (reveals.length) {
    const io = new IntersectionObserver(
      (entries, obs) => {
        entries.forEach((e) => {
          if (e.isIntersecting) {
            e.target.classList.add("show");
            obs.unobserve(e.target);
          }
        });
      },
      { threshold: 0.1 }
    );
    reveals.forEach((el) => io.observe(el));
  }
  // Khởi tạo tooltip Bootstrap (nếu có)
  if (window.bootstrap && bootstrap.Tooltip) {
    try {
      new bootstrap.Tooltip(document.body, {
        selector: '[data-bs-toggle="tooltip"]',
      });
    } catch (e) {}
  }

  // Tự ẩn thông báo alert sau 4 giây
  document.querySelectorAll(".alert").forEach((el) => {
    setTimeout(() => {
      try {
        bootstrap.Alert.getOrCreateInstance(el).close();
      } catch (e) {
        el.remove();
      }
    }, 4000);
  });

  // Ghi nhớ trạng thái mở/đóng bộ lọc nâng cao và auto-submit select
  if (panel && btn) {
    const key = "advancedFiltersOpen";
    const saved = localStorage.getItem(key);
    if (saved === "1") {
      panel.classList.remove("d-none");
      btn.classList.remove("btn-outline-primary");
      btn.classList.add("btn-primary");
    }
    btn.addEventListener("click", () => {
      const open = !panel.classList.contains("d-none");
      localStorage.setItem(key, open ? "0" : "1");
    });
    const form = panel.closest("form");
    panel
      .querySelectorAll("select")
      .forEach((sel) =>
        sel.addEventListener("change", () => form && form.submit())
      );
  }

  // Giới hạn ngày tối thiểu = hôm nay cho input date[name=date]
  document
    .querySelectorAll('input[type="date"][name="date"]')
    .forEach((inp) => {
      const d = new Date();
      const iso = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(
        2,
        "0"
      )}-${String(d.getDate()).padStart(2, "0")}`;
      inp.setAttribute("min", iso);
    });

  // Đồng bộ min/max giá
  const minPrice = document.querySelector('input[name="min_price"]');
  const maxPrice = document.querySelector('input[name="max_price"]');
  if (minPrice && maxPrice) {
    const fix = () => {
      const min = parseInt(minPrice.value || "0", 10);
      const max = parseInt(maxPrice.value || "0", 10);
      if (max && min && max < min) maxPrice.value = min;
    };
    minPrice.addEventListener("change", fix);
    maxPrice.addEventListener("change", fix);
  }

  // Toggle hiện/ẩn mật khẩu cho input-group
  document.querySelectorAll('input[type="password"]').forEach((pwd) => {
    const group = pwd.closest(".input-group");
    if (group && !group.querySelector(".toggle-password")) {
      const btnT = document.createElement("button");
      btnT.type = "button";
      btnT.className = "input-group-text bg-white border toggle-password";
      btnT.innerHTML = '<i class="bi bi-eye"></i>';
      btnT.addEventListener("click", () => {
        const isPwd = pwd.getAttribute("type") === "password";
        pwd.setAttribute("type", isPwd ? "text" : "password");
        btnT.innerHTML = isPwd
          ? '<i class="bi bi-eye-slash"></i>'
          : '<i class="bi bi-eye"></i>';
      });
      group.appendChild(btnT);
    }
  });

  // Thay thế confirm() bằng modal Bootstrap cho form có onsubmit="return confirm(...)"
  document.querySelectorAll('form[onsubmit*="confirm("]').forEach((form) => {
    form.addEventListener(
      "submit",
      (ev) => {
        if (form.dataset.confirmed === "1") return; // đã xác nhận
        ev.preventDefault();
        const m = (form.getAttribute("onsubmit") || "").match(
          /confirm\('([^']*)'\)/
        );
        const msg = m && m[1] ? m[1] : "Bạn có chắc chắn?";
        showConfirmModal(msg, () => {
          form.dataset.confirmed = "1";
          form.submit();
        });
      },
      { capture: true }
    );
  });

  // Nút quay về đầu trang
  let backBtn = document.querySelector("#backToTop");
  if (!backBtn) {
    backBtn = document.createElement("button");
    backBtn.id = "backToTop";
    backBtn.type = "button";
    backBtn.className = "btn btn-primary shadow position-fixed";
    backBtn.style.right = "16px";
    backBtn.style.bottom = "16px";
    backBtn.style.display = "none";
    backBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
    document.body.appendChild(backBtn);
    backBtn.addEventListener("click", () =>
      window.scrollTo({ top: 0, behavior: "smooth" })
    );
    window.addEventListener("scroll", () => {
      backBtn.style.display = window.scrollY > 300 ? "inline-flex" : "none";
    });
  }

  // Hàm hiển thị modal xác nhận dùng Bootstrap
  function showConfirmModal(message, onOk) {
    let modalEl = document.getElementById("confirmModalGeneric");
    if (!modalEl) {
      const html = `
<div class="modal fade" id="confirmModalGeneric" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Xác nhận</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body"><p id="confirmModalMessage" class="mb-0"></p></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-danger" id="confirmModalOk">Đồng ý</button>
      </div>
    </div>
  </div>
</div>`;
      document.body.insertAdjacentHTML("beforeend", html);
      modalEl = document.getElementById("confirmModalGeneric");
    }
    modalEl.querySelector("#confirmModalMessage").textContent = message;
    const okBtn = modalEl.querySelector("#confirmModalOk");
    const modal = window.bootstrap ? new bootstrap.Modal(modalEl) : null;
    const handler = () => {
      okBtn.removeEventListener("click", handler);
      if (modal) modal.hide();
      onOk && onOk();
    };
    okBtn.addEventListener("click", handler);
    if (modal) modal.show();
    else onOk && onOk();
  }

  // Loading state for forms with data-loading
  document
    .querySelectorAll('form[data-loading] button[type="submit"]')
    .forEach((btn) => {
      const form = btn.closest("form");
      form.addEventListener("submit", () => {
        btn.classList.add("is-loading");
        // Add spinner if not present
        if (!btn.querySelector(".spinner")) {
          const span = document.createElement("span");
          span.className = "spinner spinner-border spinner-border-sm me-2";
          span.setAttribute("role", "status");
          span.setAttribute("aria-hidden", "true");
          btn.prepend(span);
        }
      });
    });
});
