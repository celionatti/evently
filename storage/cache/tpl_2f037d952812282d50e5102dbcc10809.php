<?php

use Trees\Helper\Utils\TimeDateUtils;

?>

<?php $this->start('styles'); ?>
<style>
    .event-detail-image {
        width: 100%;
        height: 250px;
        object-fit: cover;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .event-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .tag-badge {
        background: rgba(100, 181, 246, 0.2);
        color: var(--blue-1);
        padding: 0.25rem 0.75rem;
        border-radius: 100px;
        font-size: 0.8rem;
        border: 1px solid rgba(100, 181, 246, 0.3);
    }

    .meta-item,
    .contact-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .meta-label,
    .contact-label {
        font-size: 0.8rem;
        color: var(--text-2);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .meta-value,
    .contact-value {
        color: var(--text-1);
        font-weight: 500;
    }

    .attendee-avatar {
        font-size: 2rem;
        color: var(--blue-1);
    }

    .ticket-type-badge {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-1);
        padding: 0.25rem 0.75rem;
        border-radius: 100px;
        font-size: 0.85rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .analytics-item {
        margin-bottom: 1.5rem;
    }

    .analytics-item:last-child {
        margin-bottom: 0;
    }

    .progress {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 100px;
        overflow: hidden;
    }

    .setting-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .setting-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .event-link-display {
        flex: 1;
        margin-right: 1rem;
    }

    /* Enhanced responsive design */
    @media (max-width: 767.98px) {
        .event-detail-image {
            height: 200px;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .page-header .d-flex.gap-2 {
            width: 100%;
            justify-content: stretch;
        }

        .page-header .d-flex.gap-2 .btn {
            flex: 1;
            text-align: center;
        }

        .meta-item,
        .contact-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .event-link-display {
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .dashboard-card {
            min-height: auto;
            padding: 1rem;
        }

        .stat-card {
            min-height: 120px;
        }
    }
</style>
<?php $this->end(); ?>

<?php $this->start('content'); ?>
<!-- View Event Section -->
<div id="view-event-section" class="content-section fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 gap-3 page-header">
        <div>
            <h1 class="h2 mb-1">Event Details</h1>
            <p class="text-secondary">View and manage event information and attendee data.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url("/admin/events/edit/{$event->slug}") ?>" class="btn btn-ghost btn-sm">
                <i class="bi bi-pencil me-2"></i>Edit Event
            </a>
            <a href="<?= url("/admin/events/manage") ?>" class="btn btn-pulse btn-sm">
                <i class="bi bi-arrow-left me-2"></i>Back to Events
            </a>
        </div>
    </div>

    <!-- Event Overview Card -->
    <div class="dashboard-grid-full">
        <div class="dashboard-card slide-up">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="event-image-container">
                        <img src="<?= get_image($event->event_image, "dist/img/default.png") ?>"
                            class="event-detail-image rounded shadow-sm"
                            alt="<?= htmlspecialchars($event->event_title) ?>">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="event-header mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h3 class="text-white mb-0"><?php echo $event->event_title; ?></h3>
                            <span class="badge <?php echo $this->escape($event->status == 'active' ? 'bg-success' : ($event->status == 'pending' ? 'bg-warning' : 'bg-danger')); ?> text-capitalize">
                                <i class="bi bi-<?php echo $this->escape($event->status == 'active' ? 'check-circle' : ($event->status == 'pending' ? 'clock' : 'x-circle')); ?> me-1"></i>
                                <?php echo $event->status; ?>
                            </span>
                        </div>
                        <?php if ($event->tags): ?>
                            <div class="event-tags mb-3">
                                <?php foreach (explode(',', $event->tags) as $tag): ?>
                                    <span class="tag-badge"><?php echo trim($tag); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="event-meta">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="meta-item">
                                    <i class="bi bi-calendar-event text-primary me-2"></i>
                                    <div>
                                        <div class="meta-label">Event Date</div>
                                        <div class="meta-value">
                                            <?= TimeDateUtils::create($event->event_date)->toCustomFormat('j M, Y') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="meta-item">
                                    <i class="bi bi-clock text-primary me-2"></i>
                                    <div>
                                        <div class="meta-label">Start Time</div>
                                        <div class="meta-value">
                                            <?= TimeDateUtils::create($event->start_time)->toCustomFormat('G:i A') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="meta-item">
                                    <i class="bi bi-geo-alt text-primary me-2"></i>
                                    <div>
                                        <div class="meta-label">Venue</div>
                                        <div class="meta-value text-capitalize"><?php echo $event->venue; ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="meta-item">
                                    <i class="bi bi-building text-primary me-2"></i>
                                    <div>
                                        <div class="meta-label">City</div>
                                        <div class="meta-value text-capitalize"><?php echo $event->city; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="stat-number">450</div>
            <div class="stat-label">
                <i class="bi bi-ticket-perforated me-1"></i>
                Tickets Sold
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number">500</div>
            <div class="stat-label">
                <i class="bi bi-stack me-1"></i>
                Total Tickets
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number">₦675,000</div>
            <div class="stat-label">
                <i class="bi bi-currency-exchange me-1"></i>
                Revenue
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number">90%</div>
            <div class="stat-label">
                <i class="bi bi-graph-up me-1"></i>
                Sold Rate
            </div>
        </div>
    </div>

    <!-- Event Description -->
    <div class="dashboard-grid-full">
        <div class="dashboard-card table-card slide-up">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-text-paragraph me-2"></i>
                    Event Description
                </h5>
            </div>
            <div class="card-body">
                <p class="text-light mb-0"><?php echo $event->description; ?></p>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="dashboard-grid-full">
        <div class="dashboard-card table-card slide-up">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person-lines-fill me-2"></i>
                    Contact Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="contact-item">
                            <i class="bi bi-telephone text-primary me-2"></i>
                            <div>
                                <div class="contact-label">Phone</div>
                                <div class="contact-value"><?php echo $event->phone; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contact-item">
                            <i class="bi bi-envelope text-primary me-2"></i>
                            <div>
                                <div class="contact-label">Email</div>
                                <div class="contact-value"><?php echo $event->mail; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contact-item">
                            <i class="bi bi-share text-primary me-2"></i>
                            <div>
                                <div class="contact-label">Social Media</div>
                                <div class="contact-value">
                                    <a href="<?php echo $event->social; ?>" target="_blank" class="text-primary text-decoration-none">
                                        <?php echo getExcerpt($event->social, 30); ?>
                                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Link -->
    <?php if ($event->event_link): ?>
        <div class="dashboard-grid-full">
            <div class="dashboard-card table-card slide-up">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-link-45deg me-2"></i>
                        Event Link
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="event-link-display">
                            <span class="text-break" style="font-family: monospace; color: var(--blue-1);">
                                <?php echo $event->event_link; ?>
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-ghost btn-sm" onclick="copyToClipboard('<?php echo $event->event_link; ?>')" title="Copy link">
                                <i class="bi bi-clipboard me-1"></i>Copy
                            </button>
                            <a href="<?php echo $event->event_link; ?>" target="_blank" class="btn btn-ghost btn-sm" title="Open link">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Open
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Ticket Tiers -->
    <div class="dashboard-grid-full">
        <div class="dashboard-card table-card slide-up">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-ticket-perforated me-2"></i>
                        Ticket Tiers
                    </h5>
                    <span class="badge bg-info">
                        Sales <?= $event->ticket_sales === 'open' ? 'Open' : 'Closed' ?>
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table class="table table-dark mb-0">
                        <thead>
                            <tr>
                                <th>Ticket Type</th>
                                <th>Price</th>
                                <th>Available</th>
                                <th>Sold</th>
                                <th>Revenue</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($event->tickets): ?>
                                <?php foreach ($event->tickets as $k => $ticket): ?>
                                    <tr class="fade-in" style="animation-delay: <?= $k * 0.1 ?>s;">
                                        <td data-label="Ticket Type">
                                            <div class="fw-semibold text-white"><?php echo $ticket->ticket_name; ?></div>
                                        </td>
                                        <td data-label="Price">
                                            <div class="fw-semibold text-white">₦<?php echo number_format($ticket->price); ?></div>
                                        </td>
                                        <td data-label="Available">
                                            <div class="text-center">
                                                <span class="fw-semibold text-white"><?php echo $ticket->quantity - ($ticket->sold ?? 0); ?></span>
                                                <small class="text-secondary d-block">of <?php echo $ticket->quantity; ?></small>
                                            </div>
                                        </td>
                                        <td data-label="Sold">
                                            <div class="text-center">
                                                <span class="fw-semibold text-success"><?php echo $ticket->sold ?? 0; ?></span>
                                                <small class="text-secondary d-block">
                                                    <?= round((($ticket->sold ?? 0) / $ticket->quantity) * 100) ?>% sold
                                                </small>
                                            </div>
                                        </td>
                                        <td data-label="Revenue">
                                            <div class="text-center">
                                                <div class="fw-semibold text-white">₦<?php echo number_format(($ticket->sold ?? 0) * $ticket->price); ?></div>
                                            </div>
                                        </td>
                                        <td data-label="Description">
                                            <small class="text-secondary">
                                                <?php echo $ticket->description ?: 'No description'; ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        <i class="bi bi-ticket-perforated me-2"></i>
                                        No tickets configured for this event
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendees -->
    <div class="dashboard-grid-full">
        <div class="dashboard-card table-card slide-up">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>
                        Recent Attendees
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-ghost btn-sm" onclick="exportAttendees()">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                        <a href="<?= url("/admin/events/{$event->slug}/attendees") ?>" class="btn btn-ghost btn-sm">
                            <i class="bi bi-eye me-1"></i>View All
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-wrapper">
                    <table class="table table-dark mb-0">
                        <thead>
                            <tr>
                                <th>Attendee</th>
                                <th>Ticket Type</th>
                                <th>Purchase Date</th>
                                <th>Payment Status</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($recentAttendees) && $recentAttendees): ?>
                                <?php foreach ($recentAttendees as $k => $attendee): ?>
                                    <tr class="fade-in" style="animation-delay: <?= $k * 0.1 ?>s;">
                                        <td data-label="Attendee">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="attendee-avatar">
                                                    <i class="bi bi-person-circle"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-white"><?php echo $attendee->name; ?></div>
                                                    <small class="text-secondary"><?php echo $attendee->email; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Ticket Type">
                                            <span class="ticket-type-badge"><?php echo $attendee->ticket_type; ?></span>
                                        </td>
                                        <td data-label="Purchase Date">
                                            <div class="text-center">
                                                <div class="fw-semibold text-white">
                                                    <?= $attendee->purchase_date ?>
                                                </div>
                                                <small class="text-secondary">
                                                    <?= $attendee->purchase_date ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td data-label="Payment Status">
                                            <div class="text-center">
                                                <span class="badge <?php echo $this->escape($attendee->payment_status == 'paid' ? 'bg-success' : ($attendee->payment_status == 'pending' ? 'bg-warning' : 'bg-danger')); ?>">
                                                    <i class="bi bi-<?php echo $this->escape($attendee->payment_status == 'paid' ? 'check-circle' : ($attendee->payment_status == 'pending' ? 'clock' : 'x-circle')); ?> me-1"></i>
                                                    <?php echo ucfirst($attendee->payment_status); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="Amount">
                                            <div class="text-center">
                                                <div class="fw-semibold text-white">₦<?php echo number_format($attendee->amount); ?></div>
                                            </div>
                                        </td>
                                        <td data-label="Actions">
                                            <div class="dropdown">
                                                <button class="btn btn-ghost btn-sm dropdown-toggle"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="viewAttendee(<?= $attendee->id ?>)">
                                                            <i class="bi bi-eye me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="sendTicket(<?= $attendee->id ?>)">
                                                            <i class="bi bi-envelope me-2"></i>Send Ticket
                                                        </a>
                                                    </li>
                                                    <?php if ($attendee->payment_status !== 'paid'): ?>
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-warning" href="#" onclick="markAsPaid(<?= $attendee->id ?>)">
                                                                <i class="bi bi-check-circle me-2"></i>Mark as Paid
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (isset($pagination) && $pagination): ?>
                                    <div class="card-footer">
                                        <?= $pagination ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        <i class="bi bi-people me-2"></i>
                                        No attendees yet
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (isset($recentAttendees) && count($recentAttendees) >= 5): ?>
                <div class="card-footer text-center">
                    <a href="<?= url("/admin/events/{$event->slug}/attendees") ?>" class="btn btn-ghost btn-sm">
                        <i class="bi bi-eye me-1"></i>View All Attendees
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Event Analytics -->
    <div class="dashboard-grid">
        <div class="dashboard-card table-card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up me-2"></i>
                    Sales Analytics
                </h5>
            </div>
            <div class="card-body">
                <div class="analytics-item mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary">Ticket Sales Progress</span>
                        <span class="fw-semibold text-white">90%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 90%"></div>
                    </div>
                </div>

                <div class="analytics-item mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary">Revenue Target</span>
                        <span class="fw-semibold text-white">₦675k / ₦750k</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="width: 90%; background: var(--blue-2);"></div>
                    </div>
                </div>

                <div class="analytics-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Days Until Event</span>
                        <span class="fw-semibold text-primary">
                            <?= TimeDateUtils::create($event->event_date)->diffFromNow(); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-card table-card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>
                    Event Settings
                </h5>
            </div>
            <div class="card-body">
                <div class="setting-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Ticket Sales</span>
                        <span class="badge <?php echo $this->escape($event->ticket_sales == 'open' ? 'bg-success' : 'bg-danger'); ?>">
                            <?php echo ucfirst($event->ticket_sales); ?>
                        </span>
                    </div>
                </div>

                <div class="setting-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Event Status</span>
                        <span class="badge <?php echo $this->escape($event->status == 'active' ? 'bg-success' : ($event->status == 'pending' ? 'bg-warning' : 'bg-danger')); ?>">
                            <?php echo ucfirst($event->status); ?>
                        </span>
                    </div>
                </div>

                <div class="setting-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Created</span>
                        <span class="text-white">
                            <?= TimeDateUtils::create($event->created_at)->toFriendlyFormat() ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="dashboard-grid-full">
        <div class="dashboard-card">
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?= url("/admin/events/edit/{$event->slug}") ?>" class="btn btn-pulse">
                    <i class="bi bi-pencil me-2"></i>Edit Event
                </a>
                <?php if ($event->ticket_sales === 'close'): ?>
                    <button class="btn btn-ghost" onclick="toggleTicketSales('<?= $event->slug ?>', 'open')">
                        <i class="bi bi-unlock me-2"></i>Open Ticket Sales
                    </button>
                <?php else: ?>
                    <button class="btn btn-ghost" onclick="toggleTicketSales('<?= $event->slug ?>', 'close')">
                        <i class="bi bi-lock me-2"></i>Close Ticket Sales
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-danger"
                    data-bs-toggle="modal" data-bs-target="#deleteEventModal"
                    data-event-slug="<?= $event->slug ?>">
                    <i class="bi bi-trash me-2"></i>Delete Event
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEventModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>"<?php echo $event->event_title; ?>"</strong>?</p>
                    <p class="text-danger"><strong>Warning:</strong> This will permanently delete:</p>
                    <ul class="text-danger">
                        <li>The event details</li>
                        <li>All associated tickets (<?= $event->tickets ? count($event->tickets) : 0 ?> ticket types)</li>
                        <li>All attendee records (<?= isset($recentAttendees) ? count($recentAttendees) : 0 ?> attendees)</li>
                        <li>The event image</li>
                        <li>All sales data and revenue records</li>
                    </ul>
                    <p>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteEventForm" method="POST">
                        <button type="submit" class="btn btn-danger">Delete Event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteEventModal = document.getElementById('deleteEventModal');
        const deleteEventForm = document.getElementById('deleteEventForm');

        deleteEventModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const eventSlug = button.getAttribute('data-event-slug');
            deleteEventForm.action = `/admin/events/delete/${eventSlug}`;
        });

        // Add staggered animation on page load
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.animationDelay = `${index * 0.1}s`;
            row.classList.add('fade-in');
        });
    });

    // Copy to clipboard functionality
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('Link copied to clipboard!', 'success');
        }, function(err) {
            console.error('Could not copy text: ', err);
            showToast('Failed to copy link', 'error');
        });
    }

    // Export attendees functionality
    function exportAttendees() {
        const eventSlug = '<?= $event->slug ?>';
        window.open(`<?= url('/admin/events/') ?>${eventSlug}/export-attendees`, '_blank');
        showToast('Exporting attendee data...', 'info');
    }

    // View attendee details
    function viewAttendee(attendeeId) {
        window.open(`<?= url('/admin/attendees/view/') ?>${attendeeId}`, '_blank');
    }

    // Send ticket to attendee
    function sendTicket(attendeeId) {
        if (confirm('Send ticket confirmation email to this attendee?')) {
            fetch(`<?= url('/admin/attendees/send-ticket/') ?>${attendeeId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Ticket sent successfully!', 'success');
                    } else {
                        showToast('Failed to send ticket', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
        }
    }

    // Mark attendee as paid
    function markAsPaid(attendeeId) {
        if (confirm('Mark this attendee as paid?')) {
            fetch(`<?= url('/admin/attendees/mark-paid/') ?>${attendeeId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Payment status updated!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast('Failed to update payment status', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
        }
    }

    // Toggle ticket sales
    function toggleTicketSales(eventSlug, action) {
        const actionText = action === 'open' ? 'open' : 'close';
        if (confirm(`Are you sure you want to ${actionText} ticket sales for this event?`)) {
            fetch(`<?= url('/admin/events/toggle-sales/') ?>${eventSlug}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        action: action
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(`Ticket sales ${actionText}ed successfully!`, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast('Failed to update ticket sales', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
        }
    }

    // Duplicate event
    function duplicateEvent(eventSlug) {
        if (confirm('Create a duplicate of this event? You can modify the details after creation.')) {
            fetch(`<?= url('/admin/events/duplicate/') ?>${eventSlug}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Event duplicated successfully!', 'success');
                        setTimeout(() => {
                            window.location.href = `<?= url('/admin/events/edit/') ?>${data.newEventSlug}`;
                        }, 1500);
                    } else {
                        showToast('Failed to duplicate event', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred', 'error');
                });
        }
    }

    // Print event details
    function printEventDetails() {
        window.print();
    }

    // Share event link
    function shareEvent() {
        const eventUrl = '<?= url("/events/{$event->slug}") ?>';

        if (navigator.share) {
            navigator.share({
                title: '<?= addslashes($event->event_title) ?>',
                text: 'Check out this event!',
                url: eventUrl
            }).then(() => {
                showToast('Event shared successfully!', 'success');
            }).catch((error) => {
                console.log('Error sharing:', error);
                copyToClipboard(eventUrl);
            });
        } else {
            copyToClipboard(eventUrl);
        }
    }

    // Auto-refresh data every 30 seconds
    setInterval(function() {
        // You can implement auto-refresh for ticket sales data here
        // updateTicketStats();
    }, 30000);
</script>
<?php $this->end(); ?>