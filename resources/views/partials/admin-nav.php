<?php

?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-glass">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-ghost sidebar-toggle d-lg-none" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a class="navbar-brand d-flex align-items-center gap-1" href="<?= url("/") ?>">
                <span class="brand-mark">E</span>
                <span class="fw-bold text-white">ventlyy.</span>
            </a>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <button class="btn btn-ghost dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>
                    Amisu Usman
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>