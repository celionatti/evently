<?php
?>
@section('content')
@include('nav')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg border-0">
                <div class="card-header">
                    <h5 class="mb-0 text-center text-white">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Payment Successful!
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 3rem; color: #198754;"></i>
                        <h3 class="text-white text-center mt-3">Thank you for your purchase!</h3>
                        <p class="text-white lead">Your tickets for <strong class="text-white fw-bold">{{{ $event->event_title }}}</strong> have been confirmed.</p>
                    </div>

                    <!-- Transaction Details -->
                    <div class="row mb-4">
                        <div class="col-md-8 mx-auto">
                            <div class="card border-primary">
                                <div class="card-header text-white">
                                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Transaction Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <strong class="text-white">Reference Number:</strong><br>
                                            <span class="font-monospace text-white fw-bold">{{{ $transaction->reference_id }}}</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <strong class="text-white">Amount Paid:</strong><br>
                                            <span class="text-white fs-5">₦{{{ number_format($transaction->amount, 2) }}}</span>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <strong class="text-white">Email:</strong>
                                            <span class="text-white ps-2 fw-bold">{{{ $transaction->email }}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0 text-white">
                                        <i class="bi bi-ticket-perforated me-2"></i>
                                        Your Tickets ({{{ count($attendees) }}})
                                    </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Attendee Name</th>
                                                    <th>Email</th>
                                                    <th>Ticket Code</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($attendees as $index => $attendee): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <i class="bi bi-person-circle text-muted me-2"></i>
                                                                <span class="text-capitalize fw-medium">{{{ $attendee->name }}}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">{{{ $attendee->email }}}</small>
                                                        </td>
                                                        <td>
                                                            <code class="bg-light px-2 py-1 rounded">{{{ $attendee->ticket_code }}}</code>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>Confirmed
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-primary me-2"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#ticketModal{{{ $index }}}">
                                                                <i class="bi bi-eye me-1"></i>View Ticket
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <div class="row justify-content-center">
                            <div class="col-12 mb-3">
                                <a href="/checkout/download-all-tickets/{{{ $transaction->reference_id }}}"
                                    class="btn btn-success btn-lg px-4 me-2"
                                    target="_blank">
                                    <i class="bi bi-download me-2"></i>Download All Tickets (ZIP)
                                </a>
                            </div>
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <a href="/events" class="btn btn-primary btn-lg px-4">
                                        <i class="bi bi-calendar-event me-2"></i>Browse More Events
                                    </a>
                                    <a href="/my-tickets" class="btn btn-outline-secondary btn-lg px-4">
                                        <i class="bi bi-ticket-detailed me-2"></i>View My Tickets
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ticket Modals -->
<?php foreach ($attendees as $index => $attendee): ?>
    <div class="modal fade" id="ticketModal{{{ $index }}}" tabindex="-1" aria-labelledby="ticketModalLabel{{{ $index }}}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="ticketModalLabel{{{ $index }}}">
                        <i class="bi bi-ticket-perforated me-2"></i>
                        Event Ticket - {{{ $attendee->name }}}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="ticket-preview border rounded p-4" style="background: linear-gradient(135deg, #1e88e5 0%, #0d47a1 100%); color: white;">
                        <!-- Ticket Header -->
                        <div class="row align-items-center mb-4">
                            <div class="col-8">
                                <h4 class="mb-1">{{{ $event->event_title }}}</h4>
                                <p class="mb-0 opacity-75">
                                    <i class="bi bi-calendar3 me-2"></i>
                                    {{{ date('M d, Y • g:i A', strtotime($event->start_date . ' ' . $event->start_time)) }}}
                                </p>
                            </div>
                            <div class="col-4 text-end">
                                <div class="qr-placeholder bg-white rounded p-3" style="width: 80px; height: 80px; margin-left: auto;">
                                    <div class="d-flex align-items-center justify-content-center h-100 text-dark">
                                        <i class="bi bi-qr-code" style="font-size: 2rem;"></i>
                                    </div>
                                </div>
                                <small class="d-block mt-1 opacity-75">Scan at Entry</small>
                            </div>
                        </div>

                        <!-- Ticket Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-uppercase opacity-75">Attendee Name</small>
                                    <div class="fw-bold">{{{ $attendee->name }}}</div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-uppercase opacity-75">Email</small>
                                    <div>{{{ $attendee->email }}}</div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-uppercase opacity-75">Ticket Code</small>
                                    <div class="font-monospace fw-bold">{{{ $attendee->ticket_code }}}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-uppercase opacity-75">Venue</small>
                                    <div>
                                        <i class="bi bi-geo-alt me-1"></i>
                                        {{{ $event->venue ?? 'Venue TBA' }}}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-uppercase opacity-75">Status</small>
                                    <div>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Confirmed
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-uppercase opacity-75">Transaction ID</small>
                                    <div class="font-monospace small">{{{ $transaction->reference_id }}}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Footer -->
                        <div class="border-top pt-3 mt-4">
                            <div class="row align-items-center">
                                <div class="col">
                                    <small class="opacity-75">
                                        Keep this ticket safe. You'll need it for event entry.
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <small class="opacity-75">
                                        Generated: {{{ date('M d, Y') }}}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Event Description (if available) -->
                    <?php if ($event->description): ?>
                        <div class="mt-4">
                            <h6 class="text-white"><i class="bi bi-info-circle me-2"></i>Event Details</h6>
                            <div class="card">
                                <div class="card-body">
                                    <p class="card-text text-white mb-0">
                                        {{{ substr($event->description, 0, 200) }}}<?php echo strlen($event->description) > 150 ? '...' : ''; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Close
                    </button>
                    <a href="/checkout/download-ticket/{{{ $attendee->id }}}/{{{ $transaction->reference_id }}}"
                        class="btn btn-primary"
                        target="_blank">
                        <i class="bi bi-download me-2"></i>Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

@include('footer')
@endsection

@section('scripts')
<script>
    // Confetti effect
    document.addEventListener('DOMContentLoaded', function() {
        const colors = ['#198754', '#0d6efd', '#6f42c1', '#fd7e14', '#20c997'];
        const container = document.querySelector('.container');

        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-10px';
                confetti.style.width = '10px';
                confetti.style.height = '10px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.zIndex = '9999';
                confetti.style.pointerEvents = 'none';
                confetti.style.borderRadius = '50%';

                document.body.appendChild(confetti);

                // Animate confetti falling
                let pos = -10;
                const fall = setInterval(() => {
                    pos += 3;
                    confetti.style.top = pos + 'px';
                    confetti.style.transform = 'rotate(' + (pos * 2) + 'deg)';

                    if (pos > window.innerHeight) {
                        clearInterval(fall);
                        document.body.removeChild(confetti);
                    }
                }, 50);
            }, i * 100);
        }

        // Auto-hide confetti message after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.textContent.includes('Payment successful')) {
                    alert.classList.add('fade');
                }
            });
        }, 5000);
    });

    // Add smooth animation to modal show
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.querySelector(this.getAttribute('data-bs-target'));
            modal.addEventListener('shown.bs.modal', function() {
                const ticketPreview = this.querySelector('.ticket-preview');
                ticketPreview.style.transform = 'scale(0.9)';
                ticketPreview.style.opacity = '0';
                ticketPreview.style.transition = 'all 0.3s ease';

                setTimeout(() => {
                    ticketPreview.style.transform = 'scale(1)';
                    ticketPreview.style.opacity = '1';
                }, 100);
            });
        });
    });
</script>
@endsection