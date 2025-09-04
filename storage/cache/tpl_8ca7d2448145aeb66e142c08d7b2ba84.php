<?php

use App\models\Categories;

?>

<?php $this->start('content'); ?>
<?php $this->partial('nav'); ?>

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
                    <h1><?php echo $event->event_title; ?></h1>
                    <div class="d-flex flex-wrap gap-3 align-items-center mt-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-event text-info-emphasis"></i>
                            <span><?php echo $this->escape(date('l, F j, Y', strtotime($event->event_date))); ?> • <?php echo $this->escape(date('g:i A', strtotime($event->start_time ?? '00:00:00'))); ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-geo-alt text-info-emphasis"></i>
                            <span class="text-capitalize"><?php echo $event->venue; ?>, <?php echo $event->city; ?></span>
                        </div>
                    </div>
                </div>

                <div class="countdown mt-4 reveal delay-1">
                    <i class="bi bi-clock me-2"></i>
                    <span id="countdown-timer">5 days 12:45:32</span> until event
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
                <img src="<?php echo $this->escape(get_image($event->event_image, "https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=500&auto=format&fit=crop")); ?>"
                    alt="<?php echo $event->event_title; ?>" class="event-hero-img reveal w-100 mb-4">

                <h2 class="section-title reveal">Event Details</h2>

                <div class="reveal delay-1">
                    <p><?php echo $event->description; ?></p>
                </div>

                <!-- <h3 class="mt-5 pt-3 reveal delay-2">What to Expect</h3>
                <ul class="reveal delay-3">
                    <li>World-class sound system and lighting production</li>
                    <li>Multiple bars and food vendors featuring local cuisine</li>
                    <li>VIP lounge with premium amenities and private bar</li>
                    <li>Merchandise stands with exclusive event collectibles</li>
                    <li>Safe and secure environment with professional security staff</li>
                </ul> -->
            </div>

            <!-- RIGHT COLUMN - Ticket Selection -->
            <div class="right-column">
                <div class="ticket-card sticky-top" style="top: 100px;">
                    <h4 class="mb-3">Get Tickets</h4>

                    <!-- VIP Ticket -->
                    <div class="ticket-tier">
                        <div class="tier-header">
                            <div>
                                <h5 class="mb-1">VIP Experience</h5>
                                <p class="mb-0 text-info">Front row seats + backstage access</p>
                            </div>
                            <div class="price">₦45,000</div>
                        </div>
                        <div class="quantity-selector">
                            <button class="quantity-btn decrease" data-tier="vip">-</button>
                            <input type="number" class="quantity-input" id="vip-qty" value="0" min="0" max="4" data-price="45000"
                                data-tier="vip" data-charge="1000">
                            <button class="quantity-btn increase" data-tier="vip">+</button>
                            <div class="text-info ms-2">4 left</div>
                        </div>
                    </div>

                    <!-- General Admission -->
                    <div class="ticket-tier">
                        <div class="tier-header">
                            <div>
                                <h5 class="mb-1">General Admission</h5>
                                <p class="mb-0 text-info">Standard seating</p>
                            </div>
                            <div class="price">₦15,000</div>
                        </div>
                        <div class="quantity-selector">
                            <button class="quantity-btn decrease" data-tier="general">-</button>
                            <input type="number" class="quantity-input" id="general-qty" value="0" min="0" max="10"
                                data-price="15000" data-tier="general" data-charge="500">
                            <button class="quantity-btn increase" data-tier="general">+</button>
                            <div class="text-info ms-2">Plenty available</div>
                        </div>
                    </div>

                    <!-- Group Package -->
                    <div class="ticket-tier sold-out">
                        <div class="tier-header">
                            <div>
                                <h5 class="mb-1">Group Package <span class="sold-out-badge">Sold Out</span></h5>
                                <p class="mb-0 text-info">6+ tickets (15% discount)</p>
                            </div>
                            <div class="price">₦12,750</div>
                        </div>
                        <div class="quantity-selector">
                            <button class="quantity-btn decrease" data-tier="group" disabled>-</button>
                            <input type="number" class="quantity-input" id="group-qty" value="0" min="0" max="0" data-price="12750"
                                data-tier="group" data-charge="300" disabled>
                            <button class="quantity-btn increase" data-tier="group" disabled>+</button>
                        </div>
                    </div>

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
                            <div class="d-flex justify-content-between mt-3 pt-3 border-top border-secondary">
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
            </div>
        </div>
    </div>
</section>

<?php $this->partial('footer'); ?>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content text-bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="checkoutModalLabel">Complete Your Purchase</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="checkoutForm">
                    <h6 class="mb-3">Contact Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone number</label>
                            <input type="tel" class="form-control" id="phone" required>
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
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script src="/dist/js/script.js"></script>
<script>
    // Ticket selection functionality
    const ticketData = {
        vip: {
            price: 45000,
            charge: 1000,
            available: 4,
            name: "VIP Experience"
        },
        general: {
            price: 15000,
            charge: 500,
            available: 999,
            name: "General Admission"
        },
        group: {
            price: 12750,
            charge: 300,
            available: 0,
            name: "Group Package"
        }
    };

    let selectedTickets = {
        vip: 0,
        general: 0,
        group: 0
    };

    // Set up quantity buttons
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tier = btn.dataset.tier;
            const isIncrease = btn.classList.contains('increase');
            const input = document.getElementById(`${tier}-qty`);

            if (isIncrease) {
                if (parseInt(input.value) < ticketData[tier].available) {
                    input.value = parseInt(input.value) + 1;
                }
            } else {
                if (parseInt(input.value) > 0) {
                    input.value = parseInt(input.value) - 1;
                }
            }

            selectedTickets[tier] = parseInt(input.value);
            updateOrderSummary();
        });
    });

    // Update order summary
    function updateOrderSummary() {
        const orderSummary = document.getElementById('order-summary');
        const orderTotal = document.getElementById('order-total');
        const checkoutBtn = document.getElementById('checkout-btn');
        let subtotal = 0;
        let charges = 0;
        let hasTickets = false;

        let summaryHTML = '';

        for (const tier in selectedTickets) {
            if (selectedTickets[tier] > 0) {
                hasTickets = true;
                const tierPrice = selectedTickets[tier] * ticketData[tier].price;
                const tierCharges = selectedTickets[tier] * ticketData[tier].charge;
                subtotal += tierPrice;
                charges += tierCharges;

                summaryHTML += `
            <div class="d-flex justify-content-between mb-2">
              <div>${selectedTickets[tier]}x ${ticketData[tier].name}</div>
              <div>₦${tierPrice.toLocaleString()}</div>
            </div>
            <div class="d-flex justify-content-between mb-3 text-white small">
              <div>Service charges (${selectedTickets[tier]}x)</div>
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

        for (const tier in selectedTickets) {
            if (selectedTickets[tier] > 0) {
                hasAttendees = true;
                const tierName = ticketData[tier].name;
                const quantity = selectedTickets[tier];

                const tierHeader = document.createElement('h6');
                tierHeader.className = 'mt-4 mb-3';
                tierHeader.textContent = `${tierName} - Attendee Details`;
                container.appendChild(tierHeader);

                for (let i = 1; i <= quantity; i++) {
                    const attendeeForm = document.createElement('div');
                    attendeeForm.className = 'attendee-form';
                    attendeeForm.innerHTML = `
              <div class="attendee-header">
                <h6 class="mb-0">Attendee ${i}</h6>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="attendee-${tier}-${i}-name" class="form-label">Full Name</label>
                  <input type="text" class="form-control attendee-name" id="attendee-${tier}-${i}-name" data-tier="${tier}" data-index="${i}" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="attendee-${tier}-${i}-email" class="form-label">Email Address</label>
                  <input type="email" class="form-control attendee-email" id="attendee-${tier}-${i}-email" data-tier="${tier}" data-index="${i}" required>
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

    // Form submission
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Collect form data
        const formData = {
            contact: {
                name: document.getElementById('fullName').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value
            },
            tickets: selectedTickets,
            attendees: [],
            total: document.getElementById('modal-total').textContent
        };

        // Collect attendee data
        document.querySelectorAll('.attendee-form').forEach(form => {
            const nameInput = form.querySelector('.attendee-name');
            const emailInput = form.querySelector('.attendee-email');

            if (nameInput && emailInput) {
                formData.attendees.push({
                    tier: nameInput.dataset.tier,
                    index: nameInput.dataset.index,
                    name: nameInput.value,
                    email: emailInput.value
                });
            }
        });

        // In a real application, you would send this data to your server via AJAX
        console.log('Form data:', formData);

        // Simulate AJAX call
        simulateAjaxCall(formData)
            .then(response => {
                // Redirect to Paystack
                window.location.href = response.redirectUrl;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an error processing your request. Please try again.');
            });
    });

    // Simulate AJAX call to server
    function simulateAjaxCall(formData) {
        return new Promise((resolve) => {
            // Simulate server processing time
            setTimeout(() => {
                // In a real application, this would be the Paystack payment URL generated by your server
                resolve({
                    success: true,
                    redirectUrl: 'https://paystack.com/pay/eventlyy-midnight-wave'
                });
            }, 1500);
        });
    }

    // Countdown timer
    function updateCountdown() {
        const eventDate = new Date('October 10, 2025 20:00:00').getTime();
        const now = new Date().getTime();
        const distance = eventDate - now;

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('countdown-timer').innerHTML =
            `${days} days ${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();
</script>
<?php $this->end(); ?>