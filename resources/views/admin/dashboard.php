<?php

?>

@section('content')
<!-- Dashboard Section -->
<div id="dashboard-section" class="content-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Dashboard</h1>
            <p class="text-secondary">Welcome back! Here's what's happening with your events.</p>
        </div>
        <button class="btn btn-pulse" data-section="create-event">
            <i class="bi bi-plus-circle me-2"></i>Create Event
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-number">12</div>
                <div class="fw-semibold">Active Events</div>
                <div class="small text-secondary">3 new this month</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-number">2.4K</div>
                <div class="fw-semibold">Total Tickets Sold</div>
                <div class="small text-secondary">+12% from last month</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-number">₦1.8M</div>
                <div class="fw-semibold">Revenue</div>
                <div class="small text-secondary">This month</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-number">4.2</div>
                <div class="fw-semibold">Avg. Rating</div>
                <div class="small text-secondary">From 156 reviews</div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-card">
                <h5 class="mb-3">Recent Events</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Tickets Sold</th>
                                <th>Revenue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Afrobeats Live: Midnight Wave</div>
                                    <small class="text-secondary">Eko Convention Center</small>
                                </td>
                                <td>Oct 10, 2024</td>
                                <td>450/500</td>
                                <td>₦675,000</td>
                                <td><span class="badge bg-success">Active</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">TechCon Africa 2025</div>
                                    <small class="text-secondary">Landmark Centre</small>
                                </td>
                                <td>Nov 2, 2024</td>
                                <td>320/800</td>
                                <td>₦960,000</td>
                                <td><span class="badge bg-warning">Selling</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">Laughs & Lagos</div>
                                    <small class="text-secondary">Terra Kulture Arena</small>
                                </td>
                                <td>Dec 14, 2024</td>
                                <td>89/200</td>
                                <td>₦133,500</td>
                                <td><span class="badge bg-warning">Selling</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="dashboard-card">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <button class="btn btn-pulse" data-section="create-event">
                        <i class="bi bi-plus-circle me-2"></i>Create New Event
                    </button>
                    <button class="btn btn-ghost" data-section="tickets">
                        <i class="bi bi-ticket-perforated me-2"></i>Manage Tickets
                    </button>
                    <button class="btn btn-ghost" data-section="analytics">
                        <i class="bi bi-graph-up me-2"></i>View Analytics
                    </button>
                    <button class="btn btn-ghost" data-section="payments">
                        <i class="bi bi-credit-card me-2"></i>Payment Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection