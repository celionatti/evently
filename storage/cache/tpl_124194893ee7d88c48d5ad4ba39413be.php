<?php

?>

<?php $this->start('styles'); ?>
<style>
    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>
<?php $this->end(); ?>

<?php $this->start('content'); ?>
<!-- SECURITY SETUP CONTENT -->
<div class="container py-5 flex-grow-1">
    <div class="security-container">
        <div class="step-indicator">
            <div class="step completed">
                <div class="step-number">
                    <i class="bi bi-check-lg"></i>
                </div>
                <div class="step-label">Registration</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-label">Security Setup</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Complete</div>
            </div>
        </div>

        <h2 class="section-title">Complete Your Security Setup</h2>
        <p class="section-sub">Final steps to secure your account and protect your tickets</p>

        <div class="warning-box">
            <strong><i class="bi bi-exclamation-triangle-fill"></i> Important:</strong> Your recovery phrase is your only way to reset your PIN if you forget it.
            Please write it down and store it in a safe place. Do not share it with anyone.
        </div>

        <div class="recovery-phrase-box">
            <h5><i class="bi bi-key"></i> Your Recovery Phrase</h5>
            <div class="recovery-phrase" id="recoveryPhrase">
                <?php echo $this->escape($recovery_phrase); ?>
            </div>
            <button type="button" class="btn btn-ghost mt-3" onclick="copyToClipboard()">
                <i class="bi bi-clipboard"></i> Copy to Clipboard
            </button>
        </div>

        <form action="<?php echo url('/complete-security-setup'); ?>" method="post" id="securitySetupForm">
            <div class="checkbox-group">
                <input type="checkbox" id="phraseConfirm" name="recovery_phrase_confirmed" value="1" required>
                <label for="phraseConfirm">
                    I have written down and safely stored my recovery phrase
                </label>
            </div>
            <?php if (isset($form_errors['recovery_phrase_confirmed'])): ?>
                <div class="error-message"><?= htmlspecialchars($form_errors['recovery_phrase_confirmed']) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="pin" class="form-label">Create Security PIN (4-8 digits)</label>
                <input
                    type="password"
                    class="form-control <?= has_error('pin') ? 'is-invalid' : '' ?>"
                    id="pin"
                    name="pin"
                    placeholder="Enter your 4-8 digit PIN"
                    pattern="[0-9]{4,8}"
                    maxlength="8"
                    required>
                <div class="form-text text-secondary">Your PIN will be used for sensitive operations</div>
                <?php if (has_error('pin')): ?>
                    <div class="invalid-feedback"><?= get_error('pin') ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="pin_confirmation" class="form-label">Confirm PIN</label>
                <input
                    type="password"
                    class="form-control <?= has_error('pin_confirmation') ? 'is-invalid' : '' ?>"
                    id="pin_confirmation"
                    name="pin_confirmation"
                    placeholder="Confirm your PIN"
                    pattern="[0-9]{4,8}"
                    maxlength="8"
                    required>
                <?php if (has_error('pin_confirmation')): ?>
                    <div class="invalid-feedback"><?= get_error('pin_confirmation') ?></div>
                <?php endif; ?>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn btn-pulse">
                    <i class="bi bi-shield-check"></i> Complete Security Setup
                </button>
                <a href="#" class="btn btn-ghost">
                    <i class="bi bi-arrow-left"></i> Back to Login
                </a>
            </div>
        </form>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
<script>
    function copyToClipboard() {
        const phrase = document.getElementById('recoveryPhrase').textContent.trim();
        navigator.clipboard.writeText(phrase).then(function() {
            showToast('Recovery phrase copied to clipboard!');
        }).catch(function(err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = phrase;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast('Recovery phrase copied to clipboard!');
        });
    }

    // Only allow numeric input for PIN fields
    document.getElementById('pin').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    document.getElementById('pin_confirmation').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
</script>
<?php $this->end(); ?>