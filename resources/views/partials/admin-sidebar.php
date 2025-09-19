<?php

?>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="p-3">
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="<?= url("/admin") ?>" class="sidebar-link <?= active_nav(1, 'admin', null, true) ? 'active' : '' ?>" data-section="dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url("/admin/events/manage") ?>" class="sidebar-link <?= active_nav([1, 2], ['admin', 'events']) ? 'active' : '' ?>">
                    <i class="bi bi-calendar-event"></i>
                    <span><?php echo isAdmin() ? '' : 'My' ?> Events</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url("/admin/articles/manage") ?>" class="sidebar-link <?= active_nav([1, 2], ['admin', 'articles']) ? 'active' : '' ?>">
                    <i class="bi bi-sticky"></i>
                    <span><?php echo isAdmin() ? '' : 'My' ?> Articles</span>
                </a>
            </li>
            <?php if (isAdmin()): ?>
                <li class="sidebar-item">
                    <a href="<?= url("/admin/categories/manage") ?>" class="sidebar-link <?= active_nav([1, 2], ['admin', 'categories']) ? 'active' : '' ?>">
                        <i class="bi bi-tags"></i>
                        <span>Manage Category</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?= url("/admin/users/manage") ?>" class="sidebar-link <?= active_nav([1, 2], ['admin', 'users']) ? 'active' : '' ?>">
                        <i class="bi bi-person-vcard"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?= url("/admin/advertisements/manage") ?>" class="sidebar-link <?= active_nav([1, 2], ['admin', 'advertisements']) ? 'active' : '' ?>">
                        <i class="bi bi-tv"></i>
                        <span>Manage Adverts</span>
                    </a>
                </li>
            <?php endif; ?>
            <!-- <li class="sidebar-item">
                <a href="#" class="sidebar-link" data-section="payments">
                    <i class="bi bi-credit-card"></i>
                    <span>Payment Setup</span>
                </a>
            </li> -->
            <li class="sidebar-item">
                <a href="<?= url("/admin/profile") ?>" class="sidebar-link <?= active_nav([1, 2], ['admin', 'profile']) ? 'active' : '' ?>" data-section="profile">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url("/admin/settings/manage") ?>" class="sidebar-link <?= active_nav([1, 2], ['admin', 'settings']) ? 'active' : '' ?>">
                    <i class="bi bi-sliders"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>
</aside>