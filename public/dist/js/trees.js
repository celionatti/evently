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

function copyToClipboard(text) {
  // Check if the modern Clipboard API is available and we're in a secure context
  if (navigator.clipboard && window.isSecureContext) {
    // Modern approach using Clipboard API
    navigator.clipboard
      .writeText(text)
      .then(function () {
        showToast("Link copied to clipboard!", "success");
      })
      .catch(function (err) {
        console.error("Clipboard API failed: ", err);
        // Fallback to execCommand if Clipboard API fails
        fallbackCopyToClipboard(text);
      });
  } else {
    // Fallback for older browsers or non-secure contexts
    fallbackCopyToClipboard(text);
  }
}

function fallbackCopyToClipboard(text) {
  // Create a temporary textarea element
  const textArea = document.createElement("textarea");
  textArea.value = text;

  // Make the textarea invisible but still selectable
  textArea.style.position = "fixed";
  textArea.style.left = "-999999px";
  textArea.style.top = "-999999px";
  textArea.style.opacity = "0";
  textArea.style.pointerEvents = "none";
  textArea.setAttribute("readonly", "");
  textArea.setAttribute("tabindex", "-1");

  // Add to DOM
  document.body.appendChild(textArea);

  try {
    // Select the text
    textArea.focus();
    textArea.select();
    textArea.setSelectionRange(0, text.length);

    // Try to copy using the older execCommand API
    const successful = document.execCommand("copy");

    if (successful) {
      showToast("Link copied to clipboard!", "success");
    } else {
      throw new Error("execCommand copy failed");
    }
  } catch (err) {
    console.error("Fallback copy failed: ", err);

    // Final fallback - prompt user to copy manually
    if (window.prompt) {
      window.prompt("Copy this text manually (Ctrl+C or Cmd+C):", text);
    } else {
      showToast("Unable to copy automatically. Please copy manually.", "error");
      console.log("Text to copy:", text);
    }
  } finally {
    // Clean up - remove the temporary element
    document.body.removeChild(textArea);
  }
}

// Alternative single function approach (more compact)
function copyToClipboardCompact(text) {
  const copy = async () => {
    try {
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        return true;
      } else {
        throw new Error("Clipboard API not available");
      }
    } catch (err) {
      // Fallback method
      const textArea = document.createElement("textarea");
      textArea.value = text;
      textArea.style.cssText =
        "position:fixed;left:-999999px;top:-999999px;opacity:0;";
      textArea.setAttribute("readonly", "");

      document.body.appendChild(textArea);
      textArea.select();
      textArea.setSelectionRange(0, text.length);

      try {
        const result = document.execCommand("copy");
        document.body.removeChild(textArea);
        return result;
      } catch (execErr) {
        document.body.removeChild(textArea);
        throw execErr;
      }
    }
  };

  copy()
    .then((success) => {
      if (success) {
        showToast("Link copied to clipboard!", "success");
      } else {
        throw new Error("Copy operation failed");
      }
    })
    .catch((err) => {
      console.error("Could not copy text: ", err);
      showToast("Failed to copy link", "error");

      // Final manual fallback
      if (window.prompt) {
        window.prompt("Please copy manually (Ctrl+C or Cmd+C):", text);
      }
    });
}
