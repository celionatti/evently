<?php

?>

@section('content')
@include('nav')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0 text-center text-white">Payment Successful!</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill success-icon" style="font-size: 4rem; color: #48bb78;"></i>
                    </div>

                    <h5 class="text-center text-white">Thank you for your purchase!</h5>
                    <p class="text-center text-white">Your tickets for <strong>{{{ $event->event_title }}}</strong> have been confirmed.</p>

                    <div class="alert alert-info">
                        <strong>Reference Number:</strong> {{{ $transaction->reference_id }}}<br>
                        <strong>Amount Paid:</strong> ₦{{{ number_format($transaction->amount, 2) }}}<br>
                        <strong>Email:</strong> {{{ $transaction->email }}}
                    </div>

                    <h6>Attendees:</h6>
                    <ul class="list-group mb-4">
                        <?php foreach ($attendees as $attendee): ?>
                            <li class="list-group-item">
                                {{{ $attendee->name }}} - {{{ $attendee->email }}}
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="text-center">
                        <a href="/events" class="btn btn-primary">Browse More Events</a>
                        <a href="/my-tickets" class="btn btn-ghost ms-2">View My Tickets</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('footer')
@endsection

@section('scripts')
<script>
    // Simple confetti effect
    document.addEventListener('DOMContentLoaded', function() {
        const colors = ['#64b5f6', '#1e88e5', '#0d47a1', '#48bb78'];
        const container = document.querySelector('.container');

        for (let i = 0; i < 30; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = Math.random() * 100 + 'vh';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.transform = 'rotate(' + (Math.random() * 360) + 'deg)';
            container.appendChild(confetti);
        }
    });
</script>
@endsection