const sidebar = document.getElementById("sidebar");
const sidebarToggle = document.getElementById("sidebarToggle");

// Sidebar toggle (mobile)
sidebarToggle.addEventListener("click", () => {
  sidebar.classList.toggle("show");
});
