// Initialize tooltips
document.addEventListener("DOMContentLoaded", function () {
  // Enable Bootstrap tooltips
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

// Improved toast notification function
function showToast(message, type = "info") {
  // Remove any existing toasts
  const existingToasts = document.querySelectorAll(".toast");
  existingToasts.forEach((toast) => toast.remove());

  const toast = document.createElement("div");
  toast.className = `toast align-items-center text-bg-${type} border-0 position-fixed bottom-0 end-0 p-2 m-2`;
  toast.setAttribute("role", "alert");
  toast.setAttribute("aria-live", "assertive");
  toast.setAttribute("aria-atomic", "true");
  toast.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi ${
              type === "success"
                ? "bi-check-circle-fill"
                : "bi-info-circle-fill"
            } me-2"></i> ${message}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      `;

  document.body.appendChild(toast);
  const bsToast = new bootstrap.Toast(toast);
  bsToast.show();

  // Remove toast after it's hidden
  toast.addEventListener("hidden.bs.toast", function () {
    document.body.removeChild(toast);
  });
}
