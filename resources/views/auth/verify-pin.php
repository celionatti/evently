<?php

?>

@section('content')
<div class="security-container">
    <div class="security-header">
        <div class="security-icon">
            <i class="bi bi-shield-lock"></i>
        </div>
        <h2 class="security-title">Security Verification</h2>
        <p class="security-subtitle">Please enter your security PIN to continue</p>
    </div>

    <form method="POST" action="/verify-pin" id="pinVerifyForm">
        <div class="form-group">
            <label for="pin" class="form-label">Security PIN</label>
            <div class="pin-input-container">
                <input
                    type="password"
                    class="pin-input"
                    id="pin"
                    name="pin"
                    placeholder="••••"
                    maxlength="8"
                    required
                    autocomplete="off">
            </div>
            <div class="form-text">Enter your 4-8 digit security PIN</div>
            <?php if (isset($form_errors['pin'])): ?>
                <div class="error-message">
                    <i class="bi bi-exclamation-circle"></i>
                    <?= htmlspecialchars($form_errors['pin']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="btn-container">
            <button type="submit" class="btn-pulse">
                <i class="bi bi-shield-check"></i> Verify PIN
            </button>

            <a href="/reset-pin" class="btn-link">
                <i class="bi bi-key"></i> Forgot your PIN?
            </a>
        </div>
    </form>
</div>
@endsection