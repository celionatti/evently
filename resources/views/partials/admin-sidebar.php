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
                    <span>My Events</span>
                </a>
            </li>
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
                <a href="<?= url("/admin/events/tickets-sales") ?>" class="sidebar-link">
                    <i class="bi bi-ticket-perforated"></i>
                    <span>Ticket Sales</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" data-section="analytics">
                    <i class="bi bi-graph-up"></i>
                    <span>Analytics</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" data-section="payments">
                    <i class="bi bi-credit-card"></i>
                    <span>Payment Setup</span>
                </a>
            </li>
        </ul>
    </div>
</aside>