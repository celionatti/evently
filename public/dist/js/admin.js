const sidebarLinks = document.querySelectorAll(".sidebar-link, [data-section]");
const sections = document.querySelectorAll(".content-section");
const sidebar = document.getElementById("sidebar");
const sidebarToggle = document.getElementById("sidebarToggle");

// Section switching
sidebarLinks.forEach((link) => {
  link.addEventListener("click", (e) => {
    e.preventDefault();
    const target = link.dataset.section;

    if (target) {
      sections.forEach((sec) => sec.classList.add("d-none"));
      document.getElementById(`${target}-section`).classList.remove("d-none");

      document
        .querySelectorAll(".sidebar-link")
        .forEach((l) => l.classList.remove("active"));
      document
        .querySelector(`.sidebar-link[data-section="${target}"]`)
        ?.classList.add("active");
    }
  });
});

// Sidebar toggle (mobile)
sidebarToggle.addEventListener("click", () => {
  sidebar.classList.toggle("show");
});

// Add Ticket Tier dynamically
const addTicketTier = document.getElementById("addTicketTier");
const ticketTiers = document.getElementById("ticketTiers");

addTicketTier.addEventListener("click", () => {
  const tier = document.createElement("div");
  tier.classList.add("ticket-tier");
  tier.innerHTML = `
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Tier Name *</label>
            <input type="text" class="form-control" placeholder="e.g., VIP" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Price (₦) *</label>
            <input type="number" class="form-control" placeholder="0" min="0" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Quantity *</label>
            <input type="number" class="form-control" placeholder="50" min="1" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Actions</label>
            <div class="tier-controls">
              <button type="button" class="btn btn-outline-danger btn-sm remove-tier">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Tier Description</label>
            <textarea class="form-control" rows="2" placeholder="What's included in this tier?"></textarea>
          </div>
        </div>
      `;
  ticketTiers.appendChild(tier);

  tier.querySelector(".remove-tier").addEventListener("click", () => {
    tier.remove();
  });
});
