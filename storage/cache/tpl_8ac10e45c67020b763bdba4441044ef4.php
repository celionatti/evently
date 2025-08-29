<?php

?>

<?php $this->start('content'); ?>
<!-- Events Section -->
<div id="events-section" class="content-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">My Events</h1>
            <p class="text-secondary">Manage your event listings and track performance.</p>
        </div>
        <a href="<?= url("/admin/events/create") ?>" class="btn btn-pulse flex-end" data-section="create-event">
            <i class="bi bi-plus-circle me-2"></i>Create Event
        </a>
    </div>

    <div class="dashboard-card">
        <div class="table-responsive">
            <table class="table table-dark">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Tickets</th>
                        <th>Revenue</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=100&auto=format&fit=crop"
                                    class="rounded" style="width: 60px; height: 40px; object-fit: cover;">
                                <div>
                                    <div class="fw-semibold">Afrobeats Live: Midnight Wave</div>
                                    <small class="text-secondary">Music • Eko Convention Center</small>
                                </div>
                            </div>
                        </td>
                        <td>Oct 10, 2024<br><small class="text-secondary">8:00 PM</small></td>
                        <td>450/500<br><small class="text-secondary">90% sold</small></td>
                        <td>₦675,000</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm dropdown-toggle"
                                    data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-eye me-2"></i>View</a></li>
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-graph-up me-2"></i>Analytics</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="#"><i
                                                class="bi bi-trash me-2"></i>Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://images.unsplash.com/photo-1557800636-894a64c1696f?q=80&w=100&auto=format&fit=crop"
                                    class="rounded" style="width: 60px; height: 40px; object-fit: cover;">
                                <div>
                                    <div class="fw-semibold">TechCon Africa 2025</div>
                                    <small class="text-secondary">Tech • Landmark Centre</small>
                                </div>
                            </div>
                        </td>
                        <td>Nov 2, 2024<br><small class="text-secondary">9:00 AM</small></td>
                        <td>320/800<br><small class="text-secondary">40% sold</small></td>
                        <td>₦960,000</td>
                        <td><span class="badge bg-warning">Selling</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm dropdown-toggle"
                                    data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-eye me-2"></i>View</a></li>
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-graph-up me-2"></i>Analytics</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="#"><i
                                                class="bi bi-trash me-2"></i>Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://images.unsplash.com/photo-1520975918311-56c45b53b7bf?q=80&w=100&auto=format&fit=crop"
                                    class="rounded" style="width: 60px; height: 40px; object-fit: cover;">
                                <div>
                                    <div class="fw-semibold">Laughs & Lagos</div>
                                    <small class="text-secondary">Comedy • Terra Kulture Arena</small>
                                </div>
                            </div>
                        </td>
                        <td>Dec 14, 2024<br><small class="text-secondary">7:30 PM</small></td>
                        <td>89/200<br><small class="text-secondary">44% sold</small></td>
                        <td>₦133,500</td>
                        <td><span class="badge bg-warning">Selling</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm dropdown-toggle"
                                    data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-eye me-2"></i>View</a></li>
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-pencil me-2"></i>Edit</a></li>
                                    <li><a class="dropdown-item" href="#"><i
                                                class="bi bi-graph-up me-2"></i>Analytics</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="#"><i
                                                class="bi bi-trash me-2"></i>Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>

<?php $this->end(); ?>