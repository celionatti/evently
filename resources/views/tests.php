<?php

?>

@section('content')
@include('nav')

<!-- EVENT HERO SECTION -->
<section class="event-hero">
    <div class="event-hero-bg" style="background-image: url('<?= $event->event_image ? $event->event_image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=1200&auto=format&fit=crop' ?>');">
        <div class="event-hero-overlay"></div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="event-hero-content">
                    <?php if ($event->featured): ?>
                        <span class="featured-badge-large">
                            <i class="bi bi-star-fill"></i> Featured Event
                        </span>
                    <?php endif; ?>

                    <div class="event-category-large">
                        <?php
                        $categoryIcons = [
                            'music' => 'bi-music-note-beamed',
                            'technology' => 'bi-laptop',
                            'art' => 'bi-palette',
                            'food' => 'bi-egg-fried',
                            'comedy' => 'bi-mic',
                            'sports' => 'bi-person-running',
                            'business' => 'bi-briefcase',
                            'education' => 'bi-book'
                        ];
                        $icon = $categoryIcons[strtolower($event->category)] ?? 'bi-calendar-event';
                        ?>
                        <i class="<?= $icon ?>"></i> <?= ucfirst($event->category) ?>
                    </div>

                    <h1 class="event-title-large"><?= htmlspecialchars($event->event_title) ?></h1>

                    <div class="event-meta-large">
                        <div class="event-meta-item">
                            <i class="bi bi-calendar-event"></i>
                            <div>
                                <strong><?= date('l, F j, Y', strtotime($event->event_date)) ?></strong>
                                <small><?= date('g:i A', strtotime($event->start_time ?? '00:00:00')) ?></small>
                            </div>
                        </div>

                        <div class="event-meta-item">
                            <i class="bi bi-geo-alt"></i>
                            <div>
                                <strong><?= htmlspecialchars($event->venue) ?></strong>
                                <small><?= htmlspecialchars($event->city) ?></small>
                            </div>
                        </div>

                        <?php if ($minPrice !== null): ?>
                            <div class="event-meta-item">
                                <i class="bi bi-tag"></i>
                                <div>
                                    <strong>
                                        <?php if ($minPrice === $maxPrice): ?>
                                            ₦<?= number_format($minPrice) ?>
                                        <?php else: ?>
                                            ₦<?= number_format($minPrice) ?> - ₦<?= number_format($maxPrice) ?>
                                        <?php endif; ?>
                                </div>

                                <?php if ($canEdit): ?>
                                    <div class="mt-3">
                                        <a href="/admin/events/edit/<?= $event->slug ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i> Edit Event
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                    </div>
                </div>
            </div>
</section>

<!-- EVENT DETAILS SECTION -->
<section class="container mt-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Event Status Alert -->
            <?php if ($isEventPassed): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    This event has already passed.
                </div>
            <?php elseif ($event->status !== 'active'): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    This event is currently <?= ucfirst($event->status) ?>.
                </div>
            <?php endif; ?>

            <!-- Event Description -->
            <div class="event-section">
                <h3><i class="bi bi-info-circle"></i> About This Event</h3>
                <div class="event-description-full">
                    <?= nl2br(htmlspecialchars($event->description)) ?>
                </div>
            </div>

            <!-- Event Details -->
            <div class="event-section">
                <h3><i class="bi bi-calendar-check"></i> Event Details</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-item">
                            <strong><i class="bi bi-calendar3"></i> Date & Time</strong>
                            <p>
                                <?= date('l, F j, Y', strtotime($event->event_date)) ?><br>
                                <?= date('g:i A', strtotime($event->start_time ?? '00:00:00')) ?>
                                <?php if ($event->end_date && $event->end_time): ?>
                                    - <?= date('g:i A', strtotime($event->end_time)) ?>
                                    <?php if ($event->end_date !== $event->event_date): ?>
                                        <br>Ends: <?= date('l, F j, Y', strtotime($event->end_date)) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="detail-item">
                            <strong><i class="bi bi-geo-alt-fill"></i> Venue</strong>
                            <p>
                                <?= htmlspecialchars($event->venue) ?><br>
                                <?= htmlspecialchars($event->city) ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($event->phone): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <strong><i class="bi bi-telephone"></i> Contact</strong>
                                <p>
                                    <a href="tel:<?= $event->phone ?>"><?= htmlspecialchars($event->phone) ?></a>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($event->mail): ?>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <strong><i class="bi bi-envelope"></i> Email</strong>
                                <p>
                                    <a href="mailto:<?= $event->mail ?>"><?= htmlspecialchars($event->mail) ?></a>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($event->social): ?>
                        <div class="col-md-12">
                            <div class="detail-item">
                                <strong><i class="bi bi-share"></i> Social Media</strong>
                                <p>
                                    <a href="<?= htmlspecialchars($event->social) ?>" target="_blank" rel="noopener">
                                        <?= htmlspecialchars($event->social) ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($event->event_link): ?>
                <!-- External Event Link -->
                <div class="event-section">
                    <h3><i class="bi bi-link-45deg"></i> External Event Page</h3>
                    <p>This event is also hosted on an external platform:</p>
                    <a href="<?= htmlspecialchars($event->event_link) ?>" target="_blank" rel="noopener" class="btn btn-outline-pulse">
                        <i class="bi bi-box-arrow-up-right"></i> Visit Event Page
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ticket Selection -->
            <?php if (!empty($tickets) && $event->ticket_sales === 'enabled' && !$isEventPassed): ?>
                <div class="ticket-card">
                    <h4><i class="bi bi-ticket-perforated"></i> Get Your Tickets</h4>

                    <?php if ($totalAvailable > 0): ?>
                        <div class="tickets-available mb-3">
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i>
                                <?= $totalAvailable ?> tickets available
                            </small>
                        </div>

                        <form action="/tickets/book" method="POST" id="ticketBookingForm">
                            <input type="hidden" name="event_id" value="<?= $event->id ?>">

                            <?php foreach ($tickets as $ticket): ?>
                                <?php
                                        $ticketAvailable = $ticket->quantity - ($ticket->sold ?? 0);
                                ?>
                                <?php if ($ticketAvailable > 0): ?>
                                    <div class="ticket-option">
                                        <div class="ticket-info">
                                            <div class="ticket-name">
                                                <strong><?= htmlspecialchars($ticket->ticket_name) ?></strong>
                                                <?php if ($ticket->description): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($ticket->description) ?></small>
                                                <?php endif; ?>
                                            </div>

                                            <div class="ticket-price">
                                                <?php if ($ticket->price > 0): ?>
                                                    <strong>₦<?= number_format($ticket->price) ?></strong>
                                                    <?php if ($ticket->charges > 0): ?>
                                                        <br><small class="text-muted">+ ₦<?= number_format($ticket->charges) ?> fees</small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <strong class="text-success">Free</strong>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="ticket-quantity mt-2">
                                            <label>Quantity:</label>
                                            <select name="tickets[<?= $ticket->id ?>]" class="form-select form-select-sm ticket-select"
                                                data-price="<?= $ticket->price + $ticket->charges ?>"
                                                data-ticket-name="<?= htmlspecialchars($ticket->ticket_name) ?>">
                                                <option value="0">0</option>
                                                <?php for ($i = 1; $i <= min($ticketAvailable, 10); $i++): ?>
                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>

                                            <small class="text-muted">
                                                <?= $ticketAvailable ?> available
                                            </small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="ticket-option sold-out">
                                        <div class="ticket-info">
                                            <div class="ticket-name">
                                                <strong><?= htmlspecialchars($ticket->ticket_name) ?></strong>
                                                <br><small class="text-danger">Sold Out</small>
                                            </div>

                                            <div class="ticket-price">
                                                <?php if ($ticket->price > 0): ?>
                                                    <strong>₦<?= number_format($ticket->price) ?></strong>
                                                <?php else: ?>
                                                    <strong>Free</strong>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <div class="ticket-summary mt-3" id="ticketSummary" style="display: none;">
                                <div class="summary-content">
                                    <h6>Order Summary</h6>
                                    <div id="selectedTickets"></div>
                                    <hr>
                                    <div class="total-price">
                                        <strong>Total: ₦<span id="totalPrice">0</span></strong>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-pulse w-100 mt-3" id="bookTicketsBtn" disabled>
                                <i class="bi bi-cart-plus"></i> Book Tickets
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">Sold Out</h5>
                            <p class="text-muted">All tickets for this event have been sold.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php elseif ($event->ticket_sales === 'disabled'): ?>
                <div class="ticket-card">
                    <h4><i class="bi bi-info-circle"></i> Ticket Information</h4>
                    <div class="text-center py-3">
                        <p>Tickets for this event are not sold through our platform.</p>
                        <?php if ($event->event_link): ?>
                            <a href="<?= htmlspecialchars($event->event_link) ?>" target="_blank" class="btn btn-pulse">
                                <i class="bi bi-box-arrow-up-right"></i> Get Tickets
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($isEventPassed): ?>
                <div class="ticket-card">
                    <h4><i class="bi bi-clock-history"></i> Event Concluded</h4>
                    <div class="text-center py-3">
                        <p>This event has already taken place.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Event Stats -->
            <div class="stats-card mt-4">
                <h5><i class="bi bi-graph-up"></i> Event Statistics</h5>

                <div class="stat-item">
                    <span class="stat-label">Total Tickets:</span>
                    <span class="stat-value"><?= array_sum(array_column($tickets, 'quantity')) ?></span>
                </div>

                <div class="stat-item">
                    <span class="stat-label">Tickets Sold:</span>
                    <span class="stat-value"><?= $totalSold ?></span>
                </div>

                <div class="stat-item">
                    <span class="stat-label">Available:</span>
                    <span class="stat-value <?= $totalAvailable > 0 ? 'text-success' : 'text-danger' ?>">
                        <?= $totalAvailable ?>
                    </span>
                </div>

                <?php if (array_sum(array_column($tickets, 'quantity')) > 0): ?>
                    <div class="progress mt-2">
                        <div class="progress-bar" role="progressbar"
                            style="width: <?= ($totalSold / array_sum(array_column($tickets, 'quantity'))) * 100 ?>%">
                        </div>
                    </div>
                    <small class="text-muted">
                        <?= round(($totalSold / array_sum(array_column($tickets, 'quantity'))) * 100, 1) ?>% sold
                    </small>
                <?php endif; ?>
            </div>

            <!-- Share Event -->
            <div class="share-card mt-4">
                <h5><i class="bi bi-share"></i> Share This Event</h5>

                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(getCurrentUrl()) ?>"
                        target="_blank" class="btn btn-social btn-facebook">
                        <i class="bi bi-facebook"></i>
                    </a>

                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(getCurrentUrl()) ?>&text=<?= urlencode($event->event_title) ?>"
                        target="_blank" class="btn btn-social btn-twitter">
                        <i class="bi bi-twitter"></i>
                    </a>

                    <a href="https://wa.me/?text=<?= urlencode($event->event_title . ' - ' . getCurrentUrl()) ?>"
                        target="_blank" class="btn btn-social btn-whatsapp">
                        <i class="bi bi-whatsapp"></i>
                    </a>

                    <button type="button" class="btn btn-social btn-link" onclick="copyToClipboard('<?= getCurrentUrl() ?>')">
                        <i class="bi bi-link-45deg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Events -->
<?php if (!empty($relatedEvents)): ?>
    <section class="container mt-5">
        <h3 class="mb-4">Related Events</h3>

        <div class="row">
            <?php foreach ($relatedEvents as $index => $relatedEvent): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="event-card reveal <?= $index === 1 ? 'delay-1' : ($index === 2 ? 'delay-2' : '') ?>">
                        <img src="<?= $relatedEvent->event_image ? $relatedEvent->event_image : 'https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=500&auto=format&fit=crop' ?>"
                            alt="<?= htmlspecialchars($relatedEvent->event_title) ?>" class="event-img">

                        <div class="event-content">
                            <span class="event-category">
                                <i class="<?= $categoryIcons[strtolower($relatedEvent->category)] ?? 'bi-calendar-event' ?>"></i>
                                <?= ucfirst($relatedEvent->category) ?>
                            </span>

                            <h4 class="event-title">
                                <a href="/events/<?= $relatedEvent->slug ?>"><?= htmlspecialchars($relatedEvent->event_title) ?></a>
                            </h4>

                            <div class="event-details">
                                <div class="event-detail">
                                    <i class="bi bi-calendar-event"></i>
                                    <span>
                                        <?= date('D, M j', strtotime($relatedEvent->event_date)) ?> •
                                        <?= date('g:i A', strtotime($relatedEvent->start_time ?? '00:00:00')) ?>
                                    </span>
                                </div>
                                <div class="event-detail">
                                    <i class="bi bi-geo-alt"></i>
                                    <span><?= htmlspecialchars($relatedEvent->venue) ?>, <?= htmlspecialchars($relatedEvent->city) ?></span>
                                </div>
                            </div>

                            <div class="event-footer">
                                <?php
                                    $relatedMinPrice = null;
                                    $relatedTickets = Ticket::where(['event_id' => $relatedEvent->id]) ?? [];

                                    foreach ($relatedTickets as $ticket) {
                                        if ($ticket->price > 0 && ($relatedMinPrice === null || $ticket->price < $relatedMinPrice)) {
                                            $relatedMinPrice = $ticket->price;
                                        }
                                    }
                                ?>

                                <div class="event-price">
                                    <?php if ($relatedMinPrice): ?>
                                        From ₦<?= number_format($relatedMinPrice) ?>
                                    <?php else: ?>
                                        Free
                                    <?php endif; ?>
                                </div>

                                <a href="/events/<?= $relatedEvent->slug ?>" class="btn btn-pulse btn-sm">View Event</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

@include('footer')
@endsection

@section('scripts')
<script src="/dist/js/script.js"></script>
<script>
    // Ticket booking functionality
    document.addEventListener('DOMContentLoaded', function() {
        const ticketSelects = document.querySelectorAll('.ticket-select');
        const bookBtn = document.getElementById('bookTicketsBtn');
        const ticketSummary = document.getElementById('ticketSummary');
        const selectedTicketsDiv = document.getElementById('selectedTickets');
        const totalPriceSpan = document.getElementById('totalPrice');

        if (ticketSelects.length > 0) {
            ticketSelects.forEach(select => {
                select.addEventListener('change', updateTicketSummary);
            });
        }

        function updateTicketSummary() {
            let totalPrice = 0;
            let totalTickets = 0;
            let selectedTickets = [];

            ticketSelects.forEach(select => {
                const quantity = parseInt(select.value);
                if (quantity > 0) {
                    const price = parseFloat(select.dataset.price);
                    const ticketName = select.dataset.ticketName;
                    const subtotal = price * quantity;

                    totalPrice += subtotal;
                    totalTickets += quantity;

                    selectedTickets.push({
                        name: ticketName,
                        quantity: quantity,
                        price: price,
                        subtotal: subtotal
                    });
                }
            });

            if (totalTickets > 0) {
                // Show summary
                ticketSummary.style.display = 'block';

                // Update selected tickets display
                selectedTicketsDiv.innerHTML = selectedTickets.map(ticket =>
                    `<div class="selected-ticket">
                    <span>${ticket.quantity}x ${ticket.name}</span>
                    <span>₦${ticket.subtotal.toLocaleString()}</span>
                </div>`
                ).join('');

                // Update total price
                totalPriceSpan.textContent = totalPrice.toLocaleString();

                // Enable book button
                bookBtn.disabled = false;
                bookBtn.innerHTML = `<i class="bi bi-cart-plus"></i> Book ${totalTickets} Ticket${totalTickets > 1 ? 's' : ''}`;
            } else {
                // Hide summary
                ticketSummary.style.display = 'none';

                // Disable book button
                bookBtn.disabled = true;
                bookBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Book Tickets';
            }
        }
    });

    // Copy to clipboard function
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Show success message
            const btn = event.target.closest('button');
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i>';
            btn.classList.add('btn-success');

            setTimeout(() => {
                btn.innerHTML = originalIcon;
                btn.classList.remove('btn-success');
            }, 2000);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
        });
    }

    // Scroll to ticket section
    function scrollToTickets() {
        document.querySelector('.ticket-card').scrollIntoView({
            behavior: 'smooth'
        });
    }
</script>
@endsection
</strong>
<small>Ticket Price</small>
</div>
</div>
<?php else: ?>
    <div class="event-meta-item">
        <i class="bi bi-gift"></i>
        <div>
            <strong>Free Event</strong>
            <small>No charge</small>
        </div>
    </div>
<?php endif;
