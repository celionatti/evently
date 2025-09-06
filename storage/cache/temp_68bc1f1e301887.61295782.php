<?php

?>

<?php $this->start('content'); ?>
<?php $this->partial('nav'); ?>

<div class="payment-container">
    <div class="payment-card">
        <div class="event-info">
            <h2 class="event-title"><?php echo $event->event_title; ?></h2>
            <div class="event-date">
                <i class="bi bi-calendar-event"></i>
                <span><?php echo date('l, F j, Y', strtotime($event->event_date)); ?></span>
            </div>
        </div>

        <div class="order-summary">
            <h5 class="mb-3">Order Summary</h5>
            <?php foreach ($checkoutData['selected_tickets'] as $selectedTicket): ?>
                <div class="order-item">
                    <span><?= $selectedTicket['quantity'] ?>x <?= htmlspecialchars($selectedTicket['ticket']->ticket_name) ?></span>
                    <span>₦<?= number_format($selectedTicket['amount']) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="order-item">
                <span>Total Amount</span>
                <span>₦<?= number_format($checkoutData['total_amount']) ?></span>
            </div>
        </div>

    </div>
</div>

<?php $this->partial('footer'); ?>
<?php $this->end(); ?>