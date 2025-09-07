<?php

?>

<?php $this->start('content'); ?>
<?php $this->partial('nav'); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Payment Successful!</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h5 class="text-center">Thank you for your purchase!</h5>
                    <p class="text-center">Your tickets for <strong><?php echo $event->event_title; ?></strong> have been confirmed.</p>
                    
                    <div class="alert alert-info">
                        <strong>Reference Number:</strong> <?php echo $transaction->reference_id; ?><br>
                        <strong>Amount Paid:</strong> ₦<?php echo number_format($transaction->amount, 2); ?><br>
                        <strong>Email:</strong> <?php echo $transaction->email; ?>
                    </div>

                    <h6>Attendees:</h6>
                    <ul class="list-group mb-4">
                        <?php foreach ($attendees as $attendee): ?>
                            <li class="list-group-item">
                                <?php echo $attendee->name; ?> - <?php echo $attendee->email; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="text-center">
                        <a href="/events" class="btn btn-primary">Browse More Events</a>
                        <a href="/my-tickets" class="btn btn-outline-secondary ms-2">View My Tickets</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->partial('footer'); ?>
<?php $this->end(); ?>