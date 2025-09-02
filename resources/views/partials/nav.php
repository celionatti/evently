<?php

?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-glass">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-1" href="<?= url("/") ?>">
            <!-- <span class="brand-mark">E</span>
            <span class="fw-bold text-white">ventlyy.</span> -->
            <img src="<?= get_image("dist/img/logo.png") ?>" class="img-fluid" width="45px">
        </a>
        <button class="navbar-toggler text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"><i class="bi bi-list fs-2"></i></span>
        </button>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <li class="nav-item"><a class="nav-link" href="<?= url("/events") ?>">Discover Events</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url("/about-us") ?>">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url("/admin") ?>">Dashboard</a></li>
                <?php if(isAuthenticated()): ?>
                <li class="nav-item"><a class="btn btn-ghost ms-lg-2" href="<?= url("/login") ?>">Login</a></li>
                <li class="nav-item"><a class="btn btn-pulse ms-lg-2" href="<?= url("/sign-up") ?>">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<!-- Offcanvas Mobile Nav -->
<div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="mobileNav" aria-labelledby="mobileNavLabel" style="background:rgba(6,10,20,.98)">
    <div class="offcanvas-header gap-1">
        <!-- <span class="brand-mark">E</span>
        <h5 class="offcanvas-title" id="mobileNavLabel">ventlyy.</h5> -->
        <img src="<?= get_image("dist/img/logo.png") ?>" class="img-fluid" width="30px" id="mobileNavLabel">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <a class="nav-link py-2" href="<?= url("/events") ?>">Discover Events</a>
        <a class="nav-link py-2" href="<?= url("/about-us") ?>">About Us</a>
        <a class="nav-link py-2" href="<?= url("/admin") ?>">Dashboard</a>
        <a class="btn btn-ghost w-100 mt-3" href="<?= url("/login") ?>">Login</a>
        <a class="btn btn-pulse w-100 mt-3" href="<?= url("/sign-up") ?>">Sign up</a>
    </div>
</div>