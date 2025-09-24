<?php

declare(strict_types=1);

use App\models\Categories;

?>

@section('content')
@include('nav')

@include('advert-wide', ['ads' => $advertisements])

<!-- EVENT HERO -->
<section class="event-hero">
    <div class="container">
        <div class="row gy-4">
            <div class="col-12">
                <div class="reveal">
                    <span class="chip mb-3">
                        <?php
                        $category = Categories::find($event->category);
                        $icon = getCategoryIcon($category->name);
                        ?>
                        <i class="bi <?= $icon ?>"></i> <?= ucfirst($category->name) ?>
                    </span>
                    <h1>{{{ $event->event_title }}}</h1>
                    <div class="d-flex flex-wrap gap-3 align-items-center mt-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-event text-info-emphasis"></i>
                            <span>{{ date('l, F j, Y', strtotime($event->event_date)) }} • {{ date('g:i A', strtotime($event->start_time ?? '00:00:00')) }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-geo-alt text-info-emphasis"></i>
                            <span class="text-capitalize">{{{ $event->venue }}}, {{{ $event->city }}}</span>
                        </div>
                    </div>
                </div>

                <div class="countdown mt-4 reveal delay-1">
                    <i class="bi bi-clock me-2"></i>
                    <span id="countdown-timer">0 days 00:00:00</span> until event
                </div>

                <div class="mt-3">
                    <?php
                    $eventTitle = htmlspecialchars($event->event_title, ENT_QUOTES);
                    $eventUrl = env("APP_URL") . "e/$event->event_link";
                    $eventDate = date('l, F j, Y', strtotime($event->event_date));
                    $eventTime = date('g:i A', strtotime($event->start_time ?? '00:00:00'));
                    $eventVenue = htmlspecialchars($event->venue . ', ' . $event->city, ENT_QUOTES);
                    $eventDescription = htmlspecialchars(substr($event->description, 0, 150) . '...', ENT_QUOTES);

                    // Prepare share text
                    $shareText = "$eventTitle - $eventDate at $eventTime. Join us at $eventVenue!";
                    $shareTextEncoded = urlencode($shareText);
                    $shareUrlEncoded = urlencode($eventUrl);
                    $shareTextWithUrl = urlencode($shareText . " " . $eventUrl);
                    ?>

                    <!-- Whatsapp -->
                    <a href="https://wa.me/?text={{{ $shareTextWithUrl }}}"
                        target="_blank"
                        class="btn btn-ghost action-btn">
                        <i class="bi bi-whatsapp text-success"></i> Share on WhatsApp
                    </a>
                    <!-- Twitter -->
                    <a href="https://twitter.com/intent/tweet?text={{ $shareTextEncoded }}&url={{ $shareUrlEncoded }}"
                        target="_blank"
                        class="btn btn-ghost action-btn">
                        <i class="bi bi-twitter-x bg-black text-white p-1 rounded"></i> Share on X (Twitter)
                    </a>
                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrlEncoded }}&quote={{ $shareTextEncoded }}"
                        target="_blank"
                        class="btn btn-ghost action-btn">
                        <i class="bi bi-facebook text-primary"></i> Share on Facebook
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MAIN CONTENT - TWO COLUMN LAYOUT -->
<section class="py-5">
    <div class="container">
        <div class="two-column-layout">
            <!-- LEFT COLUMN - Event Image and Details -->
            <div class="left-column">
                <img src="{{ get_image($event->event_image, "https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=500&auto=format&fit=crop") }}"
                    alt="{{{ $event->event_title }}}" class="event-hero-img reveal w-100 mb-4" loading="lazy">

                <h2 class="section-title reveal">Event Details</h2>

                <div class="reveal delay-1">
                    <p>{{{ $event->description }}}</p>
                </div>
            </div>

            <!-- RIGHT COLUMN - Ticket Selection -->
            <div class="right-column">
                <?php if ($event->ticket_sales === 'open' && $tickets): ?>
                    <div class="ticket-card sticky-top" style="top: 100px;">
                        <h4 class="mb-3">Get Tickets</h4>

                        <?php foreach ($tickets as $ticket): ?>
                            <!-- Ticket -->
                            <div class="ticket-tier <?= $ticket['sold_out'] ? 'sold-out' : '' ?>">
                                <div class="tier-header">
                                    <div>
                                        <h5 class="mb-1"><?= $ticket['name'] ?></h5>
                                        <?php if (!empty($ticket['description'])): ?>
                                            <p class="mb-0 text-info"><?= $ticket['description'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="price">₦<?= number_format((float)$ticket['price']) ?></div>
                                </div>
                                <div class="quantity-selector">
                                    <button class="quantity-btn decrease" data-tier="<?= $ticket['id'] ?>"
                                        <?= $ticket['sold_out'] ? 'disabled' : '' ?>>-</button>
                                    <input type="number" class="quantity-input" id="<?= $ticket['id'] ?>-qty" value="0"
                                        min="0" max="<?= $ticket['available'] ?>" data-price="<?= $ticket['price'] ?>"
                                        data-tier="<?= $ticket['id'] ?>" data-charge="<?= $ticket['service_charge'] ?>"
                                        <?= $ticket['sold_out'] ? 'disabled' : '' ?>>
                                    <button class="quantity-btn increase" data-tier="<?= $ticket['id'] ?>"
                                        <?= $ticket['sold_out'] ? 'disabled' : '' ?>>+</button>
                                    <?php if ($ticket['sold_out']): ?>
                                        <span class="sold-out-badge ms-2">Sold Out</span>
                                    <?php else: ?>
                                        <div class="text-info ms-2">
                                            <?= $ticket['available'] <= 5 ? "Only {$ticket['available']} left" : "Plenty available" ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Ticket Summary -->
                        <div class="mt-4 pt-3 border-top border-secondary">
                            <h5 class="mb-3">Your Order</h5>

                            <div id="order-summary">
                                <div class="text-center text-white py-3">
                                    No tickets selected yet
                                </div>
                            </div>

                            <div class="d-none" id="order-total">
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary">
                                    <div>
                                        <div class="text-white">Subtotal</div>
                                        <div class="h5 mb-0" id="subtotal-price">₦0</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-white">Charges</div>
                                        <div class="h5 mb-0" id="charges-total">₦0</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-3 pt-3 border-top border-secondary gap-2">
                                    <div>
                                        <div class="text-white">Total</div>
                                        <div class="h4 mb-0" id="total-price">₦0</div>
                                    </div>
                                    <button class="btn btn-pulse align-self-center" id="checkout-btn" data-bs-toggle="modal"
                                        data-bs-target="#checkoutModal" disabled>
                                        <i class="bi bi-ticket-perforated me-2"></i>Get Tickets
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="ticket-card sticky-top" style="top: 100px;">
                        <!-- Header Section -->
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="bi bi-lock-fill text-warning" style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="text-warning mb-2">Ticket Sales Closed</h4>
                            <p class="text-white mb-0">Online ticket sales for this event have ended.</p>
                        </div>

                        <!-- Event Status Info -->
                        <div class="alert alert-info border-0 mb-4" style="background: rgba(13, 202, 240, 0.1);">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-info-circle-fill text-info me-2"></i>
                                <strong class="text-info">What's Next?</strong>
                            </div>
                            <p class="mb-0 small">Don't worry! You may still be able to get tickets at the door or through other channels.</p>
                        </div>

                        <!-- Alternative Options -->
                        <div class="mb-4">
                            <h6 class="mb-3">
                                <i class="bi bi-lightbulb text-warning me-2"></i>
                                Still Want to Attend?
                            </h6>

                            <div class="list-group list-group-flush">
                                <div class="list-group-item bg-transparent border-secondary px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-door-open text-success me-3 mt-1"></i>
                                        <div>
                                            <h6 class="mb-1 text-success">Door Sales</h6>
                                            <p class="mb-0 small text-white">Tickets may be available at the venue entrance on the day of the event.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="list-group-item bg-transparent border-secondary px-0">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-telephone text-primary me-3 mt-1"></i>
                                        <div>
                                            <h6 class="mb-1 text-primary">Contact Organizer</h6>
                                            <p class="mb-1 small text-white">Reach out directly for last-minute availability:</p>
                                            <div class="small">
                                                <div class="mb-1">
                                                    <i class="bi bi-envelope me-1"></i>
                                                    <a href="mailto:{{{ $event->mail }}}" class="text-decoration-none fw-semibold text-white">{{{ $event->mail }}}</a>
                                                </div>
                                                <div class="mb-1">
                                                    <i class="bi bi-phone me-1"></i>
                                                    <a href="tel:{{{ $event->phone }}}" class="text-decoration-none fw-semibold text-white">{{{ $event->phone }}}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($event->social)): ?>
                                    <div class="list-group-item bg-transparent border-secondary px-0">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-share text-info me-3 mt-1"></i>
                                            <div>
                                                <h6 class="mb-1 text-info">Follow Updates</h6>
                                                <p class="mb-1 small text-white">Stay updated on social media for any announcements:</p>
                                                <a href="{{{ $event->social }}}" target="_blank" class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-arrow-up-right-square me-1"></i>
                                                    Follow Event Page
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Event Details Summary -->
                        <div class="border-top border-secondary pt-4">
                            <h6 class="mb-3">
                                <i class="bi bi-calendar-check text-primary me-2"></i>
                                Event Reminder
                            </h6>

                            <div class="small text-white">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-calendar3 me-2"></i>
                                    <span>{{ date('l, F j, Y', strtotime($event->event_date)) }}</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-clock me-2"></i>
                                    <span>{{ date('g:i A', strtotime($event->start_time ?? '00:00:00')) }}</span>
                                </div>
                                <div class="d-flex align-items-start mb-3">
                                    <i class="bi bi-geo-alt me-2 mt-1"></i>
                                    <span class="text-capitalize">{{{ $event->venue }}}, {{{ $event->city }}}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-warning" onclick="addToCalendar()">
                                <i class="bi bi-calendar-plus me-2"></i>
                                Add to Calendar
                            </button>

                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-primary" onclick="shareEvent()">
                                    <i class="bi bi-share me-2"></i>
                                    Share Event
                                </button>
                                <button class="btn btn-outline-info" onclick="getDirections()">
                                    <i class="bi bi-map me-2"></i>
                                    Directions
                                </button>
                            </div>
                        </div>

                        <!-- Footer Note -->
                        <div class="text-center mt-4 pt-3 border-top border-secondary">
                            <small class="text-danger fw-semibold">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Ticket availability and pricing may vary at the door
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

@include('advert-wide', ['ads' => $advertisements])

@include('footer')

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content text-bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="checkoutModalLabel">Complete Your Purchase</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Changed to regular form with method and action -->
                <form id="checkoutForm" method="POST" action="/checkout/tickets">

                    <!-- Hidden inputs for ticket quantities - CHANGED TO USE ID INSTEAD OF SLUG -->
                    <?php foreach ($tickets as $ticket): ?>
                        <input type="hidden" name="tickets[<?= $ticket['id'] ?>]" id="hidden-<?= $ticket['id'] ?>" value="0">
                    <?php endforeach; ?>

                    <input type="hidden" name="event_id" value="<?= $event->id ?>">
                    <input type="hidden" name="event_slug" value="<?= $event->slug ?>">

                    <h6 class="mb-3">Contact Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullName" name="contact[name]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="contact[email]" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone number</label>
                            <input type="tel" class="form-control" id="phone" name="contact[phone]" required>
                        </div>
                    </div>

                    <div id="attendeeFormsContainer" class="mt-4">
                        <!-- Attendee forms will be dynamically inserted here -->
                    </div>

                    <div class="form-check mb-3 mt-4">
                        <input class="form-check-input" type="checkbox" id="useContactForAll">
                        <label class="form-check-label" for="useContactForAll">
                            Use my contact information for all attendees
                        </label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-secondary">
                        <div class="h4 mb-0" id="modal-total">₦0</div>
                        <button type="submit" class="btn btn-pulse">
                            Proceed to Payment <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="/dist/js/script.js"></script>
<script>
    // Convert PHP ticket data to JavaScript object - PROPERLY ESCAPED
    const ticketData = <?= json_encode(array_reduce($tickets, function ($carry, $ticket) {
                            $carry[$ticket['id']] = [
                                'price' => (float)$ticket['price'],
                                'charge' => (float)$ticket['service_charge'],
                                'available' => (int)$ticket['available'],
                                'name' => $ticket['name'],
                                'maxPerPerson' => $ticket['max_per_person'] ?? 10,
                                'slug' => $ticket['slug']
                            ];
                            return $carry;
                        }, [])) ?>;

    let selectedTickets = <?= json_encode(array_fill_keys(array_column($tickets, 'id'), 0)) ?>;

    // Set up quantity buttons
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tierId = btn.dataset.tier;
            const isIncrease = btn.classList.contains('increase');
            const input = document.getElementById(`${tierId}-qty`);
            const maxAvailable = ticketData[tierId].available;
            const maxPerPerson = ticketData[tierId].maxPerPerson;

            if (isIncrease) {
                const currentValue = parseInt(input.value);
                if (currentValue < Math.min(maxAvailable, maxPerPerson)) {
                    input.value = currentValue + 1;
                }
            } else {
                if (parseInt(input.value) > 0) {
                    input.value = parseInt(input.value) - 1;
                }
            }

            selectedTickets[tierId] = parseInt(input.value);
            // Update hidden input value
            document.getElementById(`hidden-${tierId}`).value = selectedTickets[tierId];

            updateTicketAvailability();
            updateOrderSummary();
        });
    });

    // Update ticket availability display
    function updateTicketAvailability() {
        for (const tierId in ticketData) {
            const input = document.getElementById(`${tierId}-qty`);
            const available = ticketData[tierId].available;
            const selected = selectedTickets[tierId] || 0;
            const remaining = available - selected;

            // Update the availability text
            const availabilityEl = input.parentNode.querySelector('.text-info, .sold-out-badge');
            if (availabilityEl) {
                if (remaining <= 0) {
                    availabilityEl.textContent = "Sold Out";
                    availabilityEl.className = "sold-out-badge ms-2";
                } else {
                    availabilityEl.textContent = remaining <= 5 ? `Only ${remaining} left` : "Plenty available";
                    availabilityEl.className = "text-info ms-2";
                }
            }

            // Update input max value
            input.setAttribute('max', remaining);

            // Disable increase button if no more available
            const increaseBtn = input.parentNode.querySelector('.increase');
            if (increaseBtn) {
                increaseBtn.disabled = (remaining <= 0 || selected >= ticketData[tierId].maxPerPerson);
            }
        }
    }

    // Update order summary
    function updateOrderSummary() {
        const orderSummary = document.getElementById('order-summary');
        const orderTotal = document.getElementById('order-total');
        const checkoutBtn = document.getElementById('checkout-btn');
        let subtotal = 0;
        let charges = 0;
        let hasTickets = false;

        let summaryHTML = '';

        for (const tierId in selectedTickets) {
            if (selectedTickets[tierId] > 0) {
                hasTickets = true;
                const tierPrice = selectedTickets[tierId] * ticketData[tierId].price;
                const tierCharges = selectedTickets[tierId] * ticketData[tierId].charge;
                subtotal += tierPrice;
                charges += tierCharges;

                summaryHTML += `
            <div class="d-flex justify-content-between mb-2">
              <div>${selectedTickets[tierId]}x ${ticketData[tierId].name}</div>
              <div>₦${tierPrice.toLocaleString()}</div>
            </div>
            <div class="d-flex justify-content-between mb-3 text-white small">
              <div>Service charges (${selectedTickets[tierId]}x)</div>
              <div>₦${tierCharges.toLocaleString()}</div>
            </div>
          `;
            }
        }

        if (hasTickets) {
            orderSummary.innerHTML = summaryHTML;
            orderTotal.classList.remove('d-none');
            document.getElementById('subtotal-price').textContent = `₦${subtotal.toLocaleString()}`;
            document.getElementById('charges-total').textContent = `₦${charges.toLocaleString()}`;
            document.getElementById('total-price').textContent = `₦${(subtotal + charges).toLocaleString()}`;
            document.getElementById('modal-total').textContent = `₦${(subtotal + charges).toLocaleString()}`;
            checkoutBtn.disabled = false;
        } else {
            orderSummary.innerHTML = '<div class="text-center text-muted py-3">No tickets selected yet</div>';
            orderTotal.classList.add('d-none');
            checkoutBtn.disabled = true;
        }
    }

    // Generate attendee forms when modal is shown
    $('#checkoutModal').on('show.bs.modal', function() {
        generateAttendeeForms();
    });

    // Generate attendee forms based on selected tickets
    function generateAttendeeForms() {
        const container = document.getElementById('attendeeFormsContainer');
        container.innerHTML = '';

        let hasAttendees = false;
        let attendeeCount = 0;

        for (const tierId in selectedTickets) {
            if (selectedTickets[tierId] > 0) {
                hasAttendees = true;
                const tierName = ticketData[tierId].name;
                const quantity = selectedTickets[tierId];

                const tierHeader = document.createElement('h6');
                tierHeader.className = 'mt-4 mb-3';
                tierHeader.textContent = `${tierName} - Attendee Details`;
                container.appendChild(tierHeader);

                for (let i = 1; i <= quantity; i++) {
                    attendeeCount++;
                    const attendeeForm = document.createElement('div');
                    attendeeForm.className = 'attendee-form';
                    attendeeForm.innerHTML = `
                        <div class="attendee-header">
                            <h6 class="mb-0">Attendee ${i}</h6>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="attendee-${tierId}-${i}-name" class="form-label">Full Name</label>
                                <input type="text" class="form-control attendee-name" 
                                    id="attendee-${tierId}-${i}-name" 
                                    name="attendees[${attendeeCount}][name]" 
                                    data-tier="${tierId}" data-index="${i}" required>
                                <input type="hidden" name="attendees[${attendeeCount}][tier]" value="${tierId}">
                                <input type="hidden" name="attendees[${attendeeCount}][ticket_id]" value="${tierId}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="attendee-${tierId}-${i}-email" class="form-label">Email Address</label>
                                <input type="email" class="form-control attendee-email" 
                                    id="attendee-${tierId}-${i}-email" 
                                    name="attendees[${attendeeCount}][email]" 
                                    data-tier="${tierId}" data-index="${i}" required>
                            </div>
                        </div>
                    `;
                    container.appendChild(attendeeForm);
                }
            }
        }

        if (!hasAttendees) {
            container.innerHTML = '<p class="text-center text-muted">No tickets selected</p>';
        }
    }

    // Use contact info for all attendees
    document.getElementById('useContactForAll').addEventListener('change', function() {
        if (this.checked) {
            const contactName = document.getElementById('fullName').value;
            const contactEmail = document.getElementById('email').value;

            document.querySelectorAll('.attendee-name').forEach(input => {
                input.value = contactName;
            });

            document.querySelectorAll('.attendee-email').forEach(input => {
                input.value = contactEmail;
            });
        }
    });

    // Auto-fill attendee fields when contact info changes
    document.getElementById('fullName').addEventListener('blur', function() {
        if (document.getElementById('useContactForAll').checked) {
            document.querySelectorAll('.attendee-name').forEach(input => {
                input.value = this.value;
            });
        }
    });

    document.getElementById('email').addEventListener('blur', function() {
        if (document.getElementById('useContactForAll').checked) {
            document.querySelectorAll('.attendee-email').forEach(input => {
                input.value = this.value;
            });
        }
    });

    // Countdown timer using the actual event date
    function updateCountdown() {
        const eventDate = new Date(<?= strtotime($event->event_date) * 1000 ?>);
        const now = new Date().getTime();
        const distance = eventDate - now;

        if (distance < 0) {
            document.getElementById('countdown-timer').innerHTML = "Event has started";
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('countdown-timer').innerHTML =
            `${days} days ${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    function addToCalendar() {
        const eventTitle = <?= json_encode($event->event_title) ?>;
        const eventDate = "{{ date('Ymd\\THis', strtotime($event->event_date . ' ' . ($event->start_time ?? '00:00:00'))) }}";
        const eventLocation = <?= json_encode($event->venue . ', ' . $event->city) ?>;
        const eventDescription = <?= json_encode(substr($event->description, 0, 200) . '...') ?>;

        const googleCalendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(eventTitle)}&dates=${eventDate}/${eventDate}&location=${encodeURIComponent(eventLocation)}&details=${encodeURIComponent(eventDescription)}`;

        window.open(googleCalendarUrl, '_blank');
    }

    function shareEvent() {
        copyToClipboard(window.location.href);
    }

    function getDirections() {
        const address = <?= json_encode($event->venue . ', ' . $event->city) ?>;
        const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;
        window.open(mapsUrl, '_blank');
    }

    updateTicketAvailability();
    setInterval(updateCountdown, 1000);
    updateCountdown();
</script>
@endsection