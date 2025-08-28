<?php

?>

<?php $this->start('content'); ?>

<!-- AUTH CONTAINER -->
<div class="auth-container">
    <div class="auth-card">
        <!-- Login Form -->
        <form class="auth-form" id="loginForm">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="bi bi-person-circle"></i>
                </div>
                <h2 class="auth-title">Welcome Back</h2>
                <p class="auth-subtitle">Sign in to your account to continue</p>
            </div>

            <div class="mb-3">
                <label for="loginEmail" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="loginEmail" placeholder="name@example.com" required>
            </div>

            <div class="mb-3">
                <label for="loginPassword" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="loginPassword" placeholder="Enter your password" required>
                    <span class="input-group-text password-toggle" id="loginPasswordToggle">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
                <div class="form-text text-end">
                    <a href="#" class="auth-link">Forgot password?</a>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="rememberMe">
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

<?php $this->end(); ?>