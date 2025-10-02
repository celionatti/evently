<?php

?>

<?php $this->start('styles'); ?>
<style>
    /* Payment container */
    .payment-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }

    .payment-card {
        background: linear-gradient(180deg,
                rgba(255, 255, 255, 0.05),
                rgba(255, 255, 255, 0.02));
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: var(--radius-lg);
        padding: 2.5rem;
        box-shadow: var(--shadow-1);
        max-width: 700px;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .payment-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, var(--blue-2), var(--blue-3));
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }

    .event-info {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .event-title {
        color: var(--text-1);
        font-weight: 700;
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
    }

    .event-date {
        color: var(--text-2);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .amount-display {
        background: linear-gradient(135deg, var(--blue-2), var(--blue-3));
        color: white;
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        text-align: center;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-2);
    }

    .amount-label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-bottom: 0.5rem;
    }

    .amount-value {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .pay-button {
        background: linear-gradient(135deg, var(--blue-2), var(--blue-3));
        border: none;
        color: white;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 1rem 2rem;
        border-radius: 50px;
        width: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 22px rgba(30, 136, 229, 0.35);
    }

    .pay-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(30, 136, 229, 0.4);
    }

    .pay-button:disabled {
        opacity: 0.7;
        transform: none;
        box-shadow: none;
    }

    .secure-info {
        text-align: center;
        margin-top: 2rem;
        color: var(--text-2);
        font-size: 0.9rem;
    }

    .secure-badges {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-top: 1rem;
    }

    .secure-badge {
        background: rgba(255, 255, 255, 0.05);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-md);
        font-size: 0.8rem;
        color: var(--text-1);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-content {
        background: var(--bg-1);
        padding: 2rem;
        border-radius: var(--radius-lg);
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: var(--shadow-1);
    }

    .spinner {
        border: 4px solid var(--bg-1);
        border-top: 4px solid var(--blue-2);
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .order-summary {
        background: rgba(255, 255, 255, 0.05);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .order-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
        font-weight: 700;
        font-size: 1.1rem;
        padding-top: 0.75rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .payment-card {
            padding: 1.5rem;
        }

        .amount-value {
            font-size: 2rem;
        }

        .secure-badges {
            flex-direction: column;
            align-items: center;
        }
    }
</style>
<?php $this->end(); ?>

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
            <?php foreach ($selected_tickets as $selectedTicket): ?>
                <div class="order-item">
                    <span><?php echo $selectedTicket['quantity']; ?>x <?php echo htmlspecialchars($selectedTicket['ticket']->ticket_name); ?></span>
                    <span>₦<?php echo number_format($selectedTicket['amount']); ?></span>
                </div>
            <?php endforeach; ?>
            <div class="order-item">
                <span>Total Amount</span>
                <span>₦<?php echo number_format($total_amount); ?></span>
            </div>
        </div>

        <div class="amount-display">
            <div class="amount-label">Amount to Pay</div>
            <div class="amount-value">₦<?= number_format($total_amount) ?></div>
        </div>

        <form method="POST" action="/checkout/process-payment" id="paymentForm">
            <input type="hidden" name="reference" value="<?= $reference ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($contact['email']) ?>">
            <input type="hidden" name="amount" value="<?= $total_amount * 100 ?>">
            <input type="hidden" name="event_id" value="<?= $event->id ?>">

            <button type="submit" class="pay-button" id="payButton">
                <i class="bi bi-credit-card me-2"></i>
                Pay with Paystack
            </button>
        </form>

        <div class="secure-info">
            <i class="bi bi-shield-check"></i>
            Your payment is secured with 256-bit SSL encryption

            <div class="secure-badges">
                <div class="secure-badge">
                    <i class="bi bi-shield-fill-check me-1"></i>
                    SSL Secured
                </div>
                <div class="secure-badge">
                    <i class="bi bi-credit-card me-1"></i>
                    Paystack
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-content">
        <div class="spinner"></div>
        <h5>Processing Payment...</h5>
        <p>Please wait while we process your payment</p>
    </div>
</div>

<?php $this->partial('footer'); ?>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    const paymentForm = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');
    const loadingOverlay = document.getElementById('loadingOverlay');

    paymentForm.addEventListener('submit', function(e) {
        // Show loading overlay
        loadingOverlay.style.display = 'flex';

        // Disable button to prevent multiple clicks
        payButton.disabled = true;
        payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    });
</script>
<?php $this->end(); ?>