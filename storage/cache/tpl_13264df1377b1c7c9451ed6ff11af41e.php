<?php

?>

<?php $this->start('content'); ?>

<!-- AUTH CONTAINER -->
<div class="auth-container">
    <div class="auth-card">
        <a class="navbar-brand d-flex align-items-center gap-1" href="<?= url("/") ?>">
            <!-- <span class="brand-mark">E</span>
            <span class="fw-bold text-white">ventlyy.</span> -->
        </a>
        <!-- Signup Form -->
        <form action="" method="post" class="auth-form" id="signupForm">
            <div class="auth-header">
                <a href="<?php echo url("/"); ?>" class="auth-icon">
                    <!-- <i class="bi bi-person-plus"></i> -->
                    <img src="<?= get_image("dist/img/logo.png") ?>" class="img-fluid" width="40px">
                </a>
                <h2 class="auth-title">Create Account</h2>
                <p class="auth-subtitle">Join us to discover amazing events</p>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" name="name" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" value="<?= old('name') ?>" id="firstName" placeholder="John">
                    <?php if (has_error('name')): ?>
                        <div class="invalid-feedback"><?= get_error('name') ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="otherName" class="form-label">Other Name</label>
                    <input type="text" name="other_name" class="form-control <?= has_error('other_name') ? 'is-invalid' : '' ?>" value="<?= old('other_name') ?>" id="otherName" placeholder="Doe">
                    <?php if (has_error('other_name')): ?>
                        <div class="invalid-feedback"><?= get_error('other_name') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="signupEmail" class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control <?= has_error('email') ? 'is-invalid' : '' ?>" value="<?= old('email') ?>" id="signupEmail" placeholder="name@example.com">
                <?php if (has_error('email')): ?>
                    <div class="invalid-feedback"><?= get_error('email') ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="signupPassword" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>" value="<?= old('password') ?>" name="password" id="signupPassword" placeholder="Create a password">
                    <span class="input-group-text password-toggle" id="signupPasswordToggle">
                        <i class="bi bi-eye"></i>
                    </span>
                    <?php if (has_error('password')): ?>
                        <div class="invalid-feedback"><?= get_error('password') ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-text text-white">
                    Use 8 or more characters with a mix of letters, numbers & symbols
                </div>
            </div>

            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="password_confirmation" class="form-control <?= has_error('password_confirmation') ? 'is-invalid' : '' ?>" value="<?= old('password_confirmation') ?>" id="confirmPassword" placeholder="Confirm your password">
                    <span class="input-group-text password-toggle" id="confirmPasswordToggle">
                        <i class="bi bi-eye"></i>
                    </span>
                    <?php if (has_error('password_confirmation')): ?>
                        <div class="invalid-feedback"><?= get_error('password_confirmation') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input <?= has_error('terms') ? 'is-invalid' : '' ?>" name="terms" type="checkbox" id="termsAgree">
                <label class="form-check-label" for="termsAgree">
                    I agree to the <a href="#" class="auth-link">Terms of Service</a> and <a href="#" class="auth-link">Privacy Policy</a>
                </label>
                <?php if (has_error('terms')): ?>
                    <div class="invalid-feedback"><?= get_error('terms') ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-pulse w-100 mb-3">Create Account</button>

            <!-- <div class="divider">
                <span class="divider-text">Or sign up with</span>
            </div>

            <div class="social-login mb-4">
                <button type="button" class="social-btn">
                    <i class="bi bi-google"></i>
                </button>
            </div> -->

            <div class="auth-footer">
                Already have an account? <a href="<?php echo $this->escape(url("/login")); ?>" class="auth-link">Sign in</a>
            </div>
        </form>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('scripts'); ?>
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
    setupPasswordToggle('signupPasswordToggle', 'signupPassword');
    setupPasswordToggle('confirmPasswordToggle', 'confirmPassword');
</script>
<?php $this->end(); ?>