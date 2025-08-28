<?php

?>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="p-3">
        <ul class="sidebar-nav">
            <li class="sidebar-item">
                <a href="<?= url("/admin") ?>" class="sidebar-link active" data-section="dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="<?= url("/admin/events/manage") ?>" class="sidebar-link" data-section="events">
                    <i class="bi bi-calendar-event"></i>
                    <span>My Events</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" data-section="create-event">
                    <i class="bi bi-plus-circle"></i>
                    <span>Create Event</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" data-section="tickets">
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
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" data-section="attendees">
                    <i class="bi bi-people"></i>
                    <span>Attendees</span>
                </a>
            </li>
        </ul>
    </div>
</aside>