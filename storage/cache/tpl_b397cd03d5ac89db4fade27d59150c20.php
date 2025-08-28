<?php

?>

<?php $this->start('content'); ?>

<!-- AUTH CONTAINER -->
<div class="auth-container">
    <div class="auth-card">
        <!-- Tabs for switching between forms -->
        <div class="auth-tabs mb-4">
            <div class="auth-tab active" id="loginTab">Login</div>
            <div class="auth-tab" id="signupTab">Sign Up</div>
        </div>

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

            <div class="divider">
                <span class="divider-text">Or continue with</span>
            </div>

            <div class="social-login mb-4">
                <button type="button" class="social-btn">
                    <i class="bi bi-google"></i>
                </button>
                <button type="button" class="social-btn">
                    <i class="bi bi-facebook"></i>
                </button>
                <button type="button" class="social-btn">
                    <i class="bi bi-twitter"></i>
                </button>
            </div>

            <div class="auth-footer">
                Don't have an account? <a href="#" class="auth-link" id="switchToSignup">Sign up</a>
            </div>
        </form>

        <!-- Signup Form (initially hidden) -->
        <form class="auth-form d-none" id="signupForm">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h2 class="auth-title">Create Account</h2>
                <p class="auth-subtitle">Join us to discover amazing events</p>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="firstName" placeholder="John" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="lastName" placeholder="Doe" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="signupEmail" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="signupEmail" placeholder="name@example.com" required>
            </div>

            <div class="mb-3">
                <label for="signupPassword" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="signupPassword" placeholder="Create a password" required>
                    <span class="input-group-text password-toggle" id="signupPasswordToggle">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
                <div class="form-text">
                    Use 8 or more characters with a mix of letters, numbers & symbols
                </div>
            </div>

            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm your password" required>
                    <span class="input-group-text password-toggle" id="confirmPasswordToggle">
                        <i class="bi bi-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="termsAgree" required>
                <label class="form-check-label" for="termsAgree">
                    I agree to the <a href="#" class="auth-link">Terms of Service</a> and <a href="#" class="auth-link">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn btn-pulse w-100 mb-3">Create Account</button>

            <div class="divider">
                <span class="divider-text">Or sign up with</span>
            </div>

            <div class="social-login mb-4">
                <button type="button" class="social-btn">
                    <i class="bi bi-google"></i>
                </button>
                <button type="button" class="social-btn">
                    <i class="bi bi-facebook"></i>
                </button>
                <button type="button" class="social-btn">
                    <i class="bi bi-twitter"></i>
                </button>
            </div>

            <div class="auth-footer">
                Already have an account? <a href="#" class="auth-link" id="switchToLogin">Sign in</a>
            </div>
        </form>
    </div>
</div>

<?php $this->end(); ?>