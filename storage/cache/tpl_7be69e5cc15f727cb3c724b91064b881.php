<?php

declare(strict_types=1);

?>

<?php $this->start('styles'); ?>
<style>
    .stat-card {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .stat-card .stat-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 2rem;
        opacity: 0.3;
    }

    .stat-content {
        position: relative;
        z-index: 1;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #fff;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .dashboard-card {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .dashboard-card:hover {
        border-color: rgba(255, 255, 255, 0.2);
    }

    .upcoming-event-item {
        padding: 1rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .upcoming-event-item:last-child {
        border-bottom: none;
    }

    .top-event-item {
        padding: 1rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .top-event-item:last-child {
        border-bottom: none;
    }

    .rank-badge {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        font-size: 0.9rem;
    }

    .insight-item {
        text-align: center;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
    }

    .insight-value {
        font-size: 2rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 0.5rem;
    }

    .insight-label {
        font-size: 0.875rem;
        color: #a0a0a0;
    }

    .progress {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .progress-bar {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }

    .btn-pulse {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
        }
    }

    .btn-ghost {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff;
        transition: all 0.3s ease;
    }

    .btn-ghost:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
        color: #fff;
    }
</style>
<?php $this->end(); ?>

<?php $this->start('content'); ?>
<!-- Dashboard Section -->
<div id="dashboard-section" class="content-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Dashboard</h1>
            <p class="text-secondary">
                Welcome back <?php echo $this->escape($user->name . ' ' . ($user->other_name ?? '')); ?>!
                <?php if (isOrganiser()): ?>
                    Here's an overview of your events and performance.
                <?php else: ?>
                    Here's an overview of the platform's performance.
                <?php endif; ?>
            </p>
        </div>
        <a href="<?= url("/admin/events/create") ?>" class="btn btn-pulse" data-section="create-event">
            <i class="bi bi-plus-circle me-2"></i>Create Event
        </a>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-calendar-event text-primary"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($analytics['total_events']) ?></div>
                    <div class="fw-semibold">Total Events</div>
                    <div class="small text-secondary">
                        <?= $analytics['active_events'] ?> active events
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-ticket-perforated text-success"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($analytics['monthly_tickets_sold']) ?></div>
                    <div class="fw-semibold">Tickets Sold</div>
                    <div class="small <?= $analytics['ticket_growth_percentage'] >= 0 ? 'text-success' : 'text-danger' ?>">
                        <i class="bi bi-<?= $analytics['ticket_growth_percentage'] >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                        <?= abs($analytics['ticket_growth_percentage']) ?>% from last month
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar text-warning"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">₦<?= number_format($analytics['monthly_revenue']) ?></div>
                    <div class="fw-semibold">Monthly Revenue</div>
                    <div class="small text-secondary">
                        Total: ₦<?= number_format($analytics['total_revenue']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people text-info"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= number_format($analytics['confirmed_attendees']) ?></div>
                    <div class="fw-semibold">Total Attendees</div>
                    <div class="small text-secondary">
                        Avg: <?= $analytics['average_attendees_per_event'] ?> per event
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Ticket Sales Trend</h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="chart-period" id="chart-6months" checked>
                        <label class="btn btn-outline-primary" for="chart-6months">6 Months</label>
                    </div>
                </div>
                <canvas id="ticketSalesChart" width="400" height="120"></canvas>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card">
                <h5 class="mb-3">Category Performance</h5>
                <canvas id="categoryChart" width="300" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="dashboard-card">
                <h5 class="mb-3">Revenue Trend</h5>
                <canvas id="revenueChart" width="400" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Tables Row -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="dashboard-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Recent Events</h5>
                    <a href="<?= url('/admin/events/manage') ?>" class="btn btn-sm btn-outline-primary">
                        View All <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Attendees</th>
                                <th>Revenue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentEvents)): ?>
                                <?php foreach ($recentEvents as $event): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($event['event_title']) ?></div>
                                            <small class="text-secondary"><?= htmlspecialchars($event['venue'] ?? 'TBA') ?></small>
                                        </td>
                                        <td><?= date('M j, Y', strtotime($event['event_date'])) ?></td>
                                        <td><?= $event['attendee_count'] ?></td>
                                        <td>₦<?= number_format($event['revenue']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $event['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($event['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-white py-3">
                                        No events found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card mb-4">
                <h5 class="mb-3">Upcoming Events</h5>
                <?php if (!empty($upcomingEvents)): ?>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="upcoming-event-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($event['event_title']) ?></h6>
                                    <small class="text-secondary">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= date('M j, Y', strtotime($event['event_date'])) ?>
                                    </small>
                                </div>
                                <small class="badge bg-info"><?= $event['days_until'] ?> days</small>
                            </div>
                            <div class="progress mb-2" style="height: 4px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: <?= $event['sales_percentage'] ?>%"
                                    aria-valuenow="<?= $event['sales_percentage'] ?>"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-secondary">
                                <?= $event['tickets_sold'] ?>/<?= $event['total_tickets'] ?> tickets sold (<?= $event['sales_percentage'] ?>%)
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-white py-3">
                        <i class="bi bi-calendar-x mb-2" style="font-size: 2rem;"></i>
                        <p class="mb-0">No upcoming events</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <h5 class="mb-3">Top Selling Events</h5>
                <?php if (!empty($topSellingEvents)): ?>
                    <?php foreach ($topSellingEvents as $index => $event): ?>
                        <div class="top-event-item">
                            <div class="d-flex align-items-center">
                                <div class="rank-badge me-3">
                                    #<?= $index + 1 ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($event['event_title']) ?></h6>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-success">
                                            <i class="bi bi-ticket-perforated me-1"></i>
                                            <?= $event['tickets_sold'] ?> tickets
                                        </small>
                                        <small class="text-warning">
                                            ₦<?= number_format($event['revenue'] ?? 0) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-white py-3">
                        <i class="bi bi-graph-up mb-2" style="font-size: 2rem;"></i>
                        <p class="mb-0">No sales data yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="dashboard-card">
                <h5 class="mb-3">Quick Actions</h5>
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <a href="<?= url('/admin/events/create') ?>" class="btn btn-pulse w-100">
                            <i class="bi bi-plus-circle me-2"></i>Create New Event
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <a href="<?= url('/admin/events/manage') ?>" class="btn btn-ghost w-100">
                            <i class="bi bi-calendar-check me-2"></i>Manage Events
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <button class="btn btn-ghost w-100" onclick="exportDashboardData()">
                            <i class="bi bi-download me-2"></i>Export Data
                        </button>
                    </div>
                    <?php if (isAdmin()): ?>
                        <div class="col-lg-3 col-md-6">
                            <a href="<?= url('/admin/events/cleanup') ?>" class="btn btn-ghost w-100">
                                <i class="bi bi-trash3 me-2"></i>Cleanup Tools
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Insights -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="dashboard-card">
                <h5 class="mb-3">Performance Insights</h5>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="insight-item">
                            <div class="insight-value"><?= $analytics['event_completion_rate'] ?>%</div>
                            <div class="insight-label">Event Completion Rate</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="insight-item">
                            <div class="insight-value"><?= $analytics['average_attendees_per_event'] ?></div>
                            <div class="insight-label">Avg. Attendees per Event</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="insight-item">
                            <div class="insight-value"><?= $analytics['recent_activity_count'] ?></div>
                            <div class="insight-label">Events Created (30 days)</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="insight-item">
                            <div class="insight-value">
                                <?php
                                $conversionRate = $analytics['total_events'] > 0 ?
                                    round(($analytics['confirmed_attendees'] / $analytics['total_events']) * 100, 1) : 0;
                                echo $conversionRate;
                                ?>%
                            </div>
                            <div class="insight-label">Overall Conversion Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    // Chart.js configuration
    Chart.defaults.color = '#ffffff';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
    Chart.defaults.backgroundColor = 'rgba(255, 255, 255, 0.1)';

    // Ticket Sales Chart
    const ticketSalesCtx = document.getElementById('ticketSalesChart').getContext('2d');
    const ticketSalesChart = new Chart(ticketSalesCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($ticketSalesChart['labels']) ?>,
            datasets: [{
                label: 'Tickets Sold',
                data: <?= json_encode($ticketSalesChart['data']) ?>,
                borderColor: 'rgb(102, 126, 234)',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            },
            elements: {
                point: {
                    radius: 6,
                    hoverRadius: 8
                }
            }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($revenueChart['labels']) ?>,
            datasets: [{
                label: 'Revenue (₦)',
                data: <?= json_encode($revenueChart['data']) ?>,
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgb(102, 126, 234)',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            }
        }
    });

    // Category Chart (Doughnut)
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryData = <?= json_encode($categoryStats) ?>;
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(cat => cat.category_name),
            datasets: [{
                data: categoryData.map(cat => cat.event_count),
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(118, 75, 162, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(13, 202, 240, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#1a1a2e'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Export dashboard data function
    function exportDashboardData() {
        // Prepare the data arrays for easier access
        const recentEvents = <?= json_encode($recentEvents) ?>;
        const upcomingEvents = <?= json_encode($upcomingEvents) ?>;
        const topSellingEvents = <?= json_encode($topSellingEvents) ?>;
        const monthlyStats = <?= json_encode($monthlyStats) ?>;
        const categoryStats = <?= json_encode($categoryStats) ?>;

        // Create CSV content
        let csvContent = "data:text/csv;charset=utf-8,";

        // Add analytics summary
        csvContent += "Dashboard Analytics Summary\n";
        csvContent += "Metric,Value\n";
        csvContent += "Total Events,<?= $analytics['total_events'] ?>\n";
        csvContent += "Active Events,<?= $analytics['active_events'] ?>\n";
        csvContent += "Monthly Tickets Sold,<?= $analytics['monthly_tickets_sold'] ?>\n";
        csvContent += "Monthly Revenue,<?= $analytics['monthly_revenue'] ?>\n";
        csvContent += "Total Revenue,<?= $analytics['total_revenue'] ?>\n";
        csvContent += "Total Attendees,<?= $analytics['confirmed_attendees'] ?>\n";
        csvContent += "Average Attendees Per Event,<?= $analytics['average_attendees_per_event'] ?>\n";
        csvContent += "Event Completion Rate,<?= $analytics['event_completion_rate'] ?>%\n";
        csvContent += "\n";

        // Add recent events
        csvContent += "Recent Events\n";
        csvContent += "Event Title,Date,Venue,Attendees,Revenue,Status\n";
        if (recentEvents && recentEvents.length > 0) {
            recentEvents.forEach(function(event) {
                const title = (event.event_title || '').replace(/"/g, '""');
                const venue = (event.venue || 'TBA').replace(/"/g, '""');
                const date = event.event_date || '';
                const attendees = event.attendee_count || 0;
                const revenue = event.revenue || 0;
                const status = event.status || 'unknown';

                csvContent += `"${title}","${date}","${venue}",${attendees},${revenue},"${status}"\n`;
            });
        } else {
            csvContent += "No recent events found\n";
        }
        csvContent += "\n";

        // Add upcoming events
        csvContent += "Upcoming Events\n";
        csvContent += "Event Title,Date,Days Until,Tickets Sold,Total Tickets,Sales Percentage\n";
        if (upcomingEvents && upcomingEvents.length > 0) {
            upcomingEvents.forEach(function(event) {
                const title = (event.event_title || '').replace(/"/g, '""');
                const date = event.event_date || '';
                const daysUntil = event.days_until || 0;
                const ticketsSold = event.tickets_sold || 0;
                const totalTickets = event.total_tickets || 0;
                const salesPercentage = event.sales_percentage || 0;

                csvContent += `"${title}","${date}",${daysUntil},${ticketsSold},${totalTickets},${salesPercentage}%\n`;
            });
        } else {
            csvContent += "No upcoming events found\n";
        }
        csvContent += "\n";

        // Add top selling events
        csvContent += "Top Selling Events\n";
        csvContent += "Rank,Event Title,Tickets Sold,Revenue\n";
        if (topSellingEvents && topSellingEvents.length > 0) {
            topSellingEvents.forEach(function(event, index) {
                const rank = index + 1;
                const title = (event.event_title || '').replace(/"/g, '""');
                const ticketsSold = event.tickets_sold || 0;
                const revenue = event.revenue || 0;

                csvContent += `${rank},"${title}",${ticketsSold},${revenue}\n`;
            });
        } else {
            csvContent += "No sales data available\n";
        }
        csvContent += "\n";

        // Add monthly statistics
        csvContent += "Monthly Statistics (Last 6 Months)\n";
        csvContent += "Month,Events Created,Tickets Sold,Revenue\n";
        if (monthlyStats && monthlyStats.length > 0) {
            monthlyStats.forEach(function(stat) {
                const month = (stat.month || '').replace(/"/g, '""');
                const eventsCreated = stat.events_created || 0;
                const ticketsSold = stat.tickets_sold || 0;
                const revenue = stat.revenue || 0;

                csvContent += `"${month}",${eventsCreated},${ticketsSold},${revenue}\n`;
            });
        } else {
            csvContent += "No monthly statistics available\n";
        }
        csvContent += "\n";

        // Add category statistics
        csvContent += "Category Statistics\n";
        csvContent += "Category,Event Count,Tickets Sold,Revenue\n";
        if (categoryStats && categoryStats.length > 0) {
            categoryStats.forEach(function(category) {
                const categoryName = (category.category_name || 'Unknown').replace(/"/g, '""');
                const eventCount = category.event_count || 0;
                const ticketsSold = category.tickets_sold || 0;
                const revenue = category.revenue || 0;

                csvContent += `"${categoryName}",${eventCount},${ticketsSold},${revenue}\n`;
            });
        } else {
            csvContent += "No category statistics available\n";
        }

        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "dashboard_data_<?= date('Y-m-d') ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Auto-refresh data every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
</script>
<?php $this->end(); ?>