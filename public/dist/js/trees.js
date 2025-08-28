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
function showToast(message, type = "success") {
  // Remove existing toasts
  $(".toast-container").remove();

  // Create container if it doesn't exist
  if ($(".toast-container").length === 0) {
    $("body").append(
      '<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>'
    );
  }

  // Determine icon and background
  const icons = {
    success: "check-circle",
    error: "exclamation-circle",
    warning: "exclamation-triangle",
    info: "info-circle",
  };

  const bgClass = `bg-${type}`;
  const icon = icons[type] || "info-circle";

  // Create toast
  const toast = $(
    `<div class="toast show align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${icon} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>`
  );

  // Append and auto-remove
  $(".toast-container").append(toast);
  setTimeout(() => toast.remove(), 5000);
}
