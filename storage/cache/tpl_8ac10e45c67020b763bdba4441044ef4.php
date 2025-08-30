<?php

use Trees\Helper\Utils\TimeDateUtils;

?>

<?php $this->start('content'); ?>
<!-- Events Section -->
<div id="events-section" class="content-section fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 page-header">
        <div>
            <h1 class="h2 mb-1">My Events</h1>
            <p class="text-secondary">Manage your event listings and track performance.</p>
        </div>
        <a href="<?= url("/admin/events/create") ?>" class="btn btn-pulse" data-section="create-event">
            <i class="bi bi-plus-circle me-2"></i>Create Event
        </a>
    </div>

    <div class="dashboard-grid-full">
        <div class="dashboard-card table-card slide-up">
            <?php if ($events): ?>
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-event me-2"></i>
                            Event Listings
                        </h5>
                        <small class="text-secondary">
                            <?= count($events) ?> event<?= count($events) !== 1 ? 's' : '' ?> total
                        </small>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-wrapper">
                        <table class="table table-dark mb-0">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Event Link</th>
                                    <th>Date</th>
                                    <th>Tickets</th>
                                    <th>Revenue</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($events as $k => $event): ?>
                                    <tr class="fade-in" style="animation-delay: <?= $k * 0.1 ?>s;">
                                        <td data-label="Event">
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= get_image($event->event_image, "dist/img/evently.png") ?>"
                                                    class="rounded shadow-sm"
                                                    style="width: 60px; height: 40px; object-fit: cover; border: 1px solid rgba(255, 255, 255, 0.1);">
                                                <div>
                                                    <div class="fw-semibold text-white"><?php echo $event->event_title; ?></div>
                                                    <small class="text-secondary">
                                                        <?php if ($event->tags): ?>
                                                            <i class="bi bi-tags me-1"></i><?php echo $event->tags; ?> •
                                                        <?php endif; ?>
                                                        <i class="bi bi-geo-alt me-1"></i><?php echo $event->venue; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Event Link">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-break" title="<?php echo $event->event_link; ?>" style="font-family: monospace; font-size: 0.85rem;">
                                                    <?php echo getExcerpt($event->event_link, 22); ?>
                                                </span>
                                                <?php if ($event->event_link): ?>
                                                    <button class="btn btn-ghost btn-sm" onclick="copyToClipboard('<?php echo $event->event_link; ?>')" title="Copy link">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td data-label="Event Date & Time">
                                            <div class="text-center">
                                                <div class="fw-semibold text-white">
                                                    <?= TimeDateUtils::create($event->event_date)->toCustomFormat('j M, Y') ?>
                                                </div>
                                                <small class="text-secondary">
                                                    <i class="bi bi-clock me-1"></i>
                                                    <?= TimeDateUtils::create($event->start_time)->toCustomFormat('H:i A') ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td data-label="Tickets">
                                            <?php if ($event->ticket_sales == "open"): ?>
                                                <div class="text-center">
                                                    <div class="fw-semibold text-white">450/500</div>
                                                    <small class="text-success">
                                                        <i class="bi bi-graph-up me-1"></i>90% sold
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center">
                                                    <small class="text-danger">
                                                        <i class="bi bi-x-circle me-1"></i>
                                                        Sales Closed
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Revenue">
                                            <div class="text-center">
                                                <div class="fw-semibold text-white">₦675,000</div>
                                                <small class="text-secondary">Total earnings</small>
                                            </div>
                                        </td>
                                        <td data-label="Status">
                                            <div class="text-center">
                                                <span class="badge <?php echo $this->escape($event->status == 'active' ? 'bg-success' : ($event->status == 'pending' ? 'bg-warning' : 'bg-danger')); ?> text-capitalize">
                                                    <i class="bi bi-<?php echo $this->escape($event->status == 'active' ? 'check-circle' : ($event->status == 'pending' ? 'clock' : 'x-circle')); ?> me-1"></i>
                                                    <?php echo $event->status; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="Actions">
                                            <div class="dropdown">
                                                <button class="btn btn-ghost btn-sm dropdown-toggle"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical me-1"></i>
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="viewEvent(<?= $event->id ?>)">
                                                            <i class="bi bi-eye me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= url("/admin/events/edit/{$event->id}") ?>">
                                                            <i class="bi bi-pencil me-2"></i>Edit Event
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="deleteEvent(<?= $event->id ?>, '<?php echo $event->event_title; ?>')">
                                                            <i class="bi bi-trash me-2"></i>Delete Event
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination if needed -->
                <?php if (isset($pagination) && $pagination): ?>
                    <div class="card-footer">
                        <nav aria-label="Events pagination">
                            <?= $pagination ?>
                        </nav>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <h4 class="h5 mb-3">No Events Found</h4>
                    <p class="text-muted mb-4">You haven't created any events yet. Start by creating your first event to manage bookings and track performance.</p>
                    <a href="<?= url('/admin/events/create') ?>" class="btn btn-pulse">
                        <i class="bi bi-calendar2-event me-2"></i>Create Your First Event
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <!-- <div class="dashboard-grid mt-4">
        <div class="stat-card">
            <div class="stat-number">
                <?= count($events) ?>
            </div>
            <div class="stat-label">
                <i class="bi bi-calendar-event me-1"></i>
                Total Events
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">
                <?php
                $activeEvents = array_filter($events, function ($event) {
                    return $event->status === 'active';
                });
                echo count($activeEvents);
                ?>
            </div>
            <div class="stat-label">
                <i class="bi bi-check-circle me-1"></i>
                Active Events
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">₦2.1M</div>
            <div class="stat-label">
                <i class="bi bi-currency-exchange me-1"></i>
                Total Revenue
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">1,247</div>
            <div class="stat-label">
                <i class="bi bi-ticket-perforated me-1"></i>
                Tickets Sold
            </div>
        </div>
    </div> -->
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    // Copy to clipboard functionality
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Show toast notification
            showToast('Link copied to clipboard!', 'success');
        }, function(err) {
            console.error('Could not copy text: ', err);
            showToast('Failed to copy link', 'error');
        });
    }

    // View event details
    function viewEvent(eventId) {
        window.open(`<?= url('/admin/events/view/') ?>${eventId}`, '_blank');
    }

    // Delete event
    function deleteEvent(eventId, eventTitle) {
        if (confirm(`Are you sure you want to delete "${eventTitle}"? This action cannot be undone.`)) {
            fetch(`<?= url('/admin/events/delete/') ?>${eventId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Event deleted successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast('Failed to delete event', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
        }
    }

    // Add staggered animation on page load
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.1}s`;
            row.classList.add('fade-in');
        });
    });
</script>
<?php $this->end(); ?>