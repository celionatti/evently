<?php

?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-glass">
    <div class="container-fluid">
        <div class="navbar-left">
            <button class="btn sidebar-toggle d-lg-none" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a class="navbar-brand" href="<?= url("/") ?>">
                <img src="<?= get_image("dist/img/logo.png") ?>" class="img-fluid" width="30px">
                <!-- <span class="fw-bold text-white">ventlyy.</span> -->
            </a>
        </div>

        <div class="navbar-right">
            <div class="dropdown">
                <button class="btn btn-ghost dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>
                    <span class="user-name"><?= htmlspecialchars(auth()->name . ' ' . auth()->other_name) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= url("/admin/profile") ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="<?= url("/logout") ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>