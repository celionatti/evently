<?php

use Trees\Helper\Utils\TimeDateUtils;

?>

@section('styles')
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
@endsection

@section('content')
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
                            <h3 class="text-white mb-0">{{{ $event->event_title }}}</h3>
                            <span class="badge {{ $event->status == 'active' ? 'bg-success' : 'bg-secondary' }} text-capitalize">
                                <i class="bi bi-{{ $event->status == 'active' ? 'check-circle' : 'x-circle' }} me-1"></i>
                                {{{ $event->status == 'active' ? 'Active' : 'Disabled' }}}
                            </span>
                        </div>
                        <?php if (!empty($event->tags)): ?>
                            <div class="event-tags mb-3">
                                <?php foreach (explode(',', $event->tags) as $tag): ?>
                                    <span class="tag-badge">{{{ trim($tag) }}}</span>
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
                                        <div class="meta-value text-capitalize">{{{ $event->venue }}}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="meta-item">
                                    <i class="bi bi-building text-primary me-2"></i>
                                    <div>
                                        <div class="meta-label">City</div>
                                        <div class="meta-value text-capitalize">{{{ $event->city }}}</div>
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
            <div class="stat-number"><?= $ticketStats['sold_tickets'] ?? 0 ?></div>
            <div class="stat-label">
                <i class="bi bi-ticket-perforated me-1"></i>
                Tickets Sold
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?= $ticketStats['total_tickets'] ?? 0 ?></div>
            <div class="stat-label">
                <i class="bi bi-stack me-1"></i>
                Total Tickets
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number">₦<?= number_format($ticketStats['total_revenue'] ?? 0) ?></div>
            <div class="stat-label">
                <i class="bi bi-currency-exchange me-1"></i>
                Revenue
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?= $ticketStats['sales_rate'] ?? 0 ?>%</div>
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
                <p class="text-light mb-0">{{{ $event->description }}}</p>
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
                                <div class="contact-value">{{{ $event->phone }}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contact-item">
                            <i class="bi bi-envelope text-primary me-2"></i>
                            <div>
                                <div class="contact-label">Email</div>
                                <div class="contact-value">{{{ $event->mail }}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contact-item">
                            <i class="bi bi-share text-primary me-2"></i>
                            <div>
                                <div class="contact-label">Social Media</div>
                                <div class="contact-value">
                                    <a href="{{{ $event->social }}}" target="_blank" class="text-primary text-decoration-none">
                                        {{{ getExcerpt($event->social, 30) }}}
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
                                {{{ $event->event_link }}}
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-ghost btn-sm" onclick="copyToClipboard('{{{ $event->event_link }}}')" title="Copy link">
                                <i class="bi bi-clipboard me-1"></i>Copy
                            </button>
                            <a href="{{{ $event->event_link }}}" target="_blank" class="btn btn-ghost btn-sm" title="Open link">
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
                    <span class="badge <?= $event->ticket_sales === 'open' ? 'bg-success' : 'bg-danger' ?>">
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
                                    <?php
                                    $soldCount = $ticket->sold ?? 0;
                                    $available = $ticket->quantity - $soldCount;
                                    $soldPercentage = $ticket->quantity > 0 ? round(($soldCount / $ticket->quantity) * 100) : 0;
                                    $revenue = $soldCount * $ticket->price;
                                    ?>
                                    <tr class="fade-in" style="animation-delay: <?= $k * 0.1 ?>s;">
                                        <td data-label="Ticket Type">
                                            <div class="fw-semibold text-white">{{{ $ticket->ticket_name }}}</div>
                                        </td>
                                        <td data-label="Price">
                                            <div class="fw-semibold text-white">₦{{{ number_format($ticket->price) }}}</div>
                                        </td>
                                        <td data-label="Available">
                                            <div class="text-center">
                                                <span class="fw-semibold text-white"><?= $available ?></span>
                                                <small class="text-secondary d-block">of <?= $ticket->quantity ?></small>
                                            </div>
                                        </td>
                                        <td data-label="Sold">
                                            <div class="text-center">
                                                <span class="fw-semibold text-success"><?= $soldCount ?></span>
                                                <small class="text-secondary d-block">
                                                    <?= $soldPercentage ?>% sold
                                                </small>
                                            </div>
                                        </td>
                                        <td data-label="Revenue">
                                            <div class="text-center">
                                                <div class="fw-semibold text-white">₦<?= number_format($revenue) ?></div>
                                            </div>
                                        </td>
                                        <td data-label="Description">
                                            <small class="text-secondary">
                                                {{{ $ticket->description ?: 'No description' }}}
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
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($recentAttendees) && !empty($recentAttendees)): ?>
                                <?php foreach ($recentAttendees as $k => $attendee): ?>
                                    <tr class="fade-in" style="animation-delay: <?= $k * 0.1 ?>s;">
                                        <td data-label="Attendee">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="attendee-avatar">
                                                    <i class="bi bi-person-circle"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-white">{{{ $attendee->name }}}</div>
                                                    <small class="text-secondary">{{{ $attendee->email }}}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Ticket Type">
                                            <span class="ticket-type-badge">{{{ $attendee->ticket_name ?? 'Unknown' }}}</span>
                                        </td>
                                        <td data-label="Purchase Date">
                                            <div class="text-center">
                                                <div class="fw-semibold text-white">
                                                    <?= TimeDateUtils::create($attendee->created_at)->toCustomFormat('j M, Y') ?>
                                                </div>
                                                <small class="text-secondary">
                                                    <?= TimeDateUtils::create($attendee->created_at)->toCustomFormat('G:i A') ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td data-label="Status">
                                            <div class="text-center">
                                                <?php
                                                $statusClass = match ($attendee->status) {
                                                    'confirmed' => 'bg-success',
                                                    'pending' => 'bg-warning',
                                                    'cancelled' => 'bg-danger',
                                                    'checked' => 'bg-info',
                                                    default => 'bg-secondary'
                                                };

                                                $statusIcon = match ($attendee->status) {
                                                    'confirmed' => 'check-circle',
                                                    'pending' => 'clock',
                                                    'cancelled' => 'x-circle',
                                                    'checked' => 'check2-circle',
                                                    default => 'question-circle'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <i class="bi bi-<?= $statusIcon ?> me-1"></i>
                                                    {{{ ucfirst($attendee->status ?? 'Unknown') }}}
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="Amount">
                                            <div class="text-center">
                                                <div class="fw-semibold text-white">₦{{{ number_format($attendee->amount) }}}</div>
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
                                                        <a class="dropdown-item disabled" href="#" onclick="sendTicket(<?= $attendee->id ?>)">
                                                            <i class="bi bi-envelope me-2"></i>Send Ticket
                                                        </a>
                                                    </li>
                                                    <?php if ($attendee->status !== 'checked'): ?>
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li>
                                                            <form action="{{ url("/admin/attendees/check-in/$attendee->id/$event->slug") }}" method="post" onsubmit="return confirm('Are you sure you want to check in ticket?');">
                                                                <button type="submit" class="dropdown-item text-dark">
                                                                    <i class="bi bi-check2-circle me-2"></i>Mark as Checked In
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
                    <?php if (isset($pagination) && $pagination): ?>
                        <div class="card-footer">
                            <?= $pagination ?>
                        </div>
                    <?php endif; ?>
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
                        <span class="fw-semibold text-white"><?= $ticketStats['sales_rate'] ?? 0 ?>%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: <?= $ticketStats['sales_rate'] ?? 0 ?>%"></div>
                    </div>
                </div>

                <div class="analytics-item mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary">Revenue Progress</span>
                        <span class="fw-semibold text-white">₦<?= number_format($ticketStats['total_revenue'] ?? 0) ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <?php
                        $revenueProgress = ($ticketStats['total_revenue'] > 0 && $ticketStats['total_tickets'] > 0) ?
                            min(100, ($ticketStats['sales_rate'] ?? 0)) : 0;
                        ?>
                        <div class="progress-bar" style="width: <?= $revenueProgress ?>%; background: var(--blue-2);"></div>
                    </div>
                </div>

                <div class="analytics-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Days Until Event</span>
                        <span class="fw-semibold text-primary">
                            <?php
                            $eventDate = new DateTime($event->event_date);
                            $now = new DateTime();
                            $interval = $now->diff($eventDate);

                            if ($eventDate < $now) {
                                echo "Event passed";
                            } else {
                                echo $interval->days . " days";
                            }
                            ?>
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
                        <span class="badge {{ $event->ticket_sales == 'open' ? 'bg-success' : 'bg-danger' }}">
                            {{{ ucfirst($event->ticket_sales) }}}
                        </span>
                    </div>
                </div>

                <div class="setting-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Event Status</span>
                        <span class="badge {{ $event->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{{ ucfirst($event->status) }}}
                        </span>
                    </div>
                </div>

                <div class="setting-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Total Attendees</span>
                        <span class="text-white fw-semibold">
                            <?= $ticketStats['total_attendees'] ?? 0 ?>
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
                    <form action="{{ url("/admin/events/ticket-status") }}" method="post" onsubmit="return confirm('Are you sure you want to update ticket sales?');">
                        <input type="hidden" name="event_slug" value="<?= $event->slug ?>">
                        <input type="hidden" name="ticket_sales" value="open">
                        <button type="submit" class="btn btn-ghost">
                            <i class="bi bi-unlock me-2"></i>Open Ticket Sales
                        </button>
                    </form>
                <?php else: ?>
                    <form action="{{ url("/admin/events/ticket-status") }}" method="post" onsubmit="return confirm('Are you sure you want to update ticket sales?');">
                        <input type="hidden" name="event_slug" value="<?= $event->slug ?>">
                        <input type="hidden" name="ticket_sales" value="close">
                        <button type="submit" class="btn btn-ghost">
                            <i class="bi bi-lock me-2"></i>Close Ticket Sales
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($event->status === 'disable'): ?>
                    <form action="{{ url("/admin/events/status") }}" method="post" onsubmit="return confirm('Are you sure you want to update event status?');">
                        <input type="hidden" name="event_slug" value="<?= $event->slug ?>">
                        <input type="hidden" name="status" value="active">
                        <button type="submit" class="btn btn-ghost">
                            <i class="bi bi-check-circle me-2"></i>Activate Event
                        </button>
                    </form>
                <?php else: ?>
                    <form action="{{ url("/admin/events/status") }}" method="post" onsubmit="return confirm('Are you sure you want to update event status?');">
                        <input type="hidden" name="event_slug" value="<?= $event->slug ?>">
                        <input type="hidden" name="status" value="disable">
                        <button type="submit" class="btn btn-ghost">
                            <i class="bi bi-pause-circle me-2"></i>Disable Event
                        </button>
                    </form>
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
                    <p>Are you sure you want to delete <strong>"{{{ $event->event_title }}}"</strong>?</p>
                    <p class="text-danger"><strong>Warning:</strong> This will permanently delete:</p>
                    <ul class="text-danger">
                        <li>The event details</li>
                        <li>All associated tickets (<?= $event->tickets ? count($event->tickets) : 0 ?> ticket types)</li>
                        <li>All attendee records (<?= $ticketStats['total_attendees'] ?? 0 ?> attendees)</li>
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
@endsection

@section('scripts')
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

    // Export attendees functionality
    function exportAttendees() {
        const eventSlug = '<?= $event->slug ?>';
        window.open(`<?= url('/admin/events/') ?>${eventSlug}/export-attendees`, '_blank');
        showToast('Exporting attendee data...', 'info');
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
</script>
@endsection