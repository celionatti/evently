// Year in footer
document.getElementById("y").textContent = new Date().getFullYear();

// Sticky glass enhancement for shadow on scroll
const nav = document.querySelector(".navbar-glass");
const navShadow = () => {
  if (window.scrollY > 8) {
    nav.style.boxShadow = "0 6px 24px rgba(0,0,0,.35)";
  } else {
    nav.style.boxShadow = "none";
  }
};
navShadow();
window.addEventListener("scroll", navShadow);

// Filter chips functionality
document.querySelectorAll(".filter-chip").forEach((chip) => {
  chip.addEventListener("click", function () {
    // Remove active class from all chips in the same group
    this.parentElement.querySelectorAll(".filter-chip").forEach((c) => {
      c.classList.remove("active");
    });

    // Add active class to clicked chip
    this.classList.add("active");

    // In a real application, you would filter events here
    console.log("Filtering by:", this.textContent);
  });
});

// IntersectionObserver for reveal animations
const revealItems = document.querySelectorAll(".reveal");
const io = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("in");
        io.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.14 }
);
revealItems.forEach((el) => io.observe(el));

// Ticket modal: hydrate event title from triggers
const ticketModal = document.getElementById("ticketModal");
// ticketModal.addEventListener("show.bs.modal", (e) => {
//   const btn = e.relatedTarget;
//   const evName = btn?.dataset.event || "Tickets";
//   const tier = btn?.dataset.tier;
//   ticketModal.querySelector("[data-event-label]").textContent = evName;

//   // Preselect tier if provided
//   const tierSelect = ticketModal.querySelector('select[name="tier"]');
//   if (tier) {
//     [...tierSelect.options].forEach((o) => (o.selected = o.text === tier));
//   } else {
//     tierSelect.selectedIndex = 0;
//   }

//   // Reset counts & totals
//   ticketModal.querySelector('input[name="qty"]').value = 2;
//   updateTotals();
// });

// Pricing logic for modal
const priceMap = { General: 15000, VIP: 45000, Group: 60000 };
const feesRate = 0.07;

function formatNaira(n) {
  return new Intl.NumberFormat("en-NG", {
    style: "currency",
    currency: "NGN",
    maximumFractionDigits: 0,
  }).format(n);
}

// function updateTotals() {
//   const tier = ticketModal.querySelector('select[name="tier"]').value;
//   const qty = parseInt(
//     ticketModal.querySelector('input[name="qty"]').value || 0,
//     10
//   );
//   const base = priceMap[tier] || 0;
//   const subtotal = base * qty;
//   const fees = Math.round(subtotal * feesRate);
//   const total = subtotal + fees;
//   ticketModal.querySelector("[data-subtotal]").textContent =
//     formatNaira(subtotal);
//   ticketModal.querySelector("[data-fees]").textContent = formatNaira(fees);
//   ticketModal.querySelector("[data-total]").textContent = formatNaira(total);
// }

["change", "keyup", "input"].forEach((evt) => {
  ticketModal.addEventListener(evt, (e) => {
    if (e.target.matches('select[name="tier"], input[name="qty"]'))
      updateTotals();
  });
});

// Simple validation for quick checkout
(() => {
  const form = document.getElementById("quickCheckout");
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    if (!form.checkValidity()) {
      e.stopPropagation();
      form.classList.add("was-validated");
      return;
    }
    // Simulate handoff to payment
    const btn = form.querySelector('button[type="submit"]');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing…';
    setTimeout(() => {
      btn.disabled = false;
      btn.innerHTML = original;
      const toast = document.createElement("div");
      toast.className =
        "toast align-items-center text-bg-success border-0 position-fixed top-0 start-50 translate-middle-x mt-4";
      toast.setAttribute("role", "status");
      toast.setAttribute("aria-live", "polite");
      toast.setAttribute("aria-atomic", "true");
      toast.innerHTML =
        '<div class="d-flex"><div class="toast-body">Great! Check your email for confirmation. 🎟️</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
      document.body.appendChild(toast);
      const t = new bootstrap.Toast(toast, { delay: 3000 });
      t.show();
      t._element.addEventListener("hidden.bs.toast", () => toast.remove());
    }, 1200);
  });
})();

// Accessibility: remove autoplay if reduced motion
if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
  try {
    heroGlide.update({ autoplay: false });
    loveGlide.update({ autoplay: false });
  } catch (e) {}
}
