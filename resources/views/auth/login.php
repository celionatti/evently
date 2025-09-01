<?php

?>

@section('content')

<!-- AUTH CONTAINER -->
<div class="auth-container">
    <div class="auth-card">
        <a class="navbar-brand d-flex align-items-center gap-1" href="<?= url("/") ?>">
            <!-- <span class="brand-mark">E</span>
            <span class="fw-bold text-white">ventlyy.</span> -->
        </a>
        <!-- Login Form -->
        <form action="" method="post" class="auth-form" id="loginForm">
            <div class="auth-header">
                <a href="{{{ url("/") }}}" class="auth-icon">
                    <!-- <i class="bi bi-person-circle"></i> -->
                    <img src="<?= get_image("dist/img/logo.png") ?>" class="img-fluid" width="40px">
                </a>
                <h2 class="auth-title">Welcome Back</h2>
                <p class="auth-subtitle">Sign in to your account to continue</p>
            </div>

            <div class="mb-3">
                <label for="loginEmail" class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" value="<?= old('email') ?>" id="loginEmail" placeholder="name@example.com">
                <?php if (has_error('email')): ?>
                    <div class="invalid-feedback"><?= get_error('email') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="loginPassword" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" value="<?= old('password') ?>" id="loginPassword" placeholder="Enter your password">
                    <span class="input-group-text password-toggle" id="loginPasswordToggle">
                        <i class="bi bi-eye"></i>
                    </span>
                    <?php if (has_error('password')): ?>
                        <div class="invalid-feedback"><?= get_error('password') ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-text text-end">
                    <a href="{{ url("/forget-password") }}" class="auth-link">Forgot password?</a>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" name="remember" type="checkbox" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Remember me</label>
            </div>

            <button type="submit" class="btn btn-pulse w-100 mb-3">Sign In</button>

            <!-- <div class="divider">
                <span class="divider-text">Or continue with</span>
            </div> -->

            <!-- <div class="social-login mb-4">
                <button type="button" class="social-btn">
                    <i class="bi bi-google"></i>
                </button>
            </div> -->

            <div class="auth-footer">
                Don't have an account? <a href="<?= url("/sign-up") ?>" class="auth-link" id="switchToSignup">Sign up</a>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Toggle password visibility
    function setupPasswordToggle(toggleId, inputId) {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);

        toggle.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                toggle.innerHTML = '<i class="bi bi-eye-slash"></i>';
            } else {
                input.type = 'password';
                toggle.innerHTML = '<i class="bi bi-eye"></i>';
            }
        });
    }

    // Set up password toggles
    setupPasswordToggle('loginPasswordToggle', 'loginPassword');
</script>
@endsection