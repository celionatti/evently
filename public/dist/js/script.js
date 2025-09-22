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

// Accessibility: remove autoplay if reduced motion
if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
  try {
    heroGlide.update({ autoplay: false });
    loveGlide.update({ autoplay: false });
  } catch (e) {}
}
