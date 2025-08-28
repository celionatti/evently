<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->escape($this->getTitle()); ?></title>
    <?php $this->content('header'); ?>
    <link rel="stylesheet" href="/dist/css/bootstrap-icons.css">
    <!-- Bootstrap CSS -->
    <link href="/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Glide.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide/dist/css/glide.core.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@glidejs/glide/dist/css/glide.theme.min.css">
    <style>
        :root {
            /* Three blues: light, mid, deep */
            --blue-1: #64b5f6;
            --blue-2: #1e88e5;
            --blue-3: #0d47a1;
            /* Base dark palette */
            --bg-0: #0b1220;
            --bg-1: #0e1526;
            --bg-2: #101a30;
            --text-1: #e2e8f0;
            --text-2: #9fb3c8;
            --glass: white;
            --radius-lg: 18px;
            --radius-md: 14px;
            --radius-sm: 10px;
            --shadow-1: 0 10px 30px rgba(0, 0, 0, 0.35);
            --shadow-2: 0 6px 18px rgba(0, 0, 0, 0.25);
            --shadow-focus: 0 0 0 0.25rem rgba(30, 136, 229, 0.35);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial,
                "Apple Color Emoji", "Segoe UI Emoji";
            background: radial-gradient(1200px 600px at 75% -100px,
                    rgba(30, 136, 229, 0.25),
                    transparent 70%),
                radial-gradient(800px 400px at -10% 20%,
                    rgba(13, 71, 161, 0.25),
                    transparent 60%),
                linear-gradient(180deg, var(--bg-0), var(--bg-1) 30%, var(--bg-2) 100%);
            color: var(--text-1);
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Glassmorphic sticky navbar */
        .navbar-glass {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: rgba(13, 19, 35, 0.48);
            backdrop-filter: saturate(1.2) blur(14px);
            -webkit-backdrop-filter: saturate(1.2) blur(14px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .navbar .nav-link {
            color: var(--text-2);
            transition: 0.25s ease;
        }

        .navbar .nav-link:hover,
        .navbar .nav-link:focus {
            color: var(--blue-1);
        }

        .brand-mark {
            display: inline-grid;
            place-items: center;
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--blue-2), var(--blue-3));
            border-radius: 10px;
            color: white;
            font-weight: 800;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-2);
        }

        /* Primary button (custom) */
        .btn-pulse {
            --c1: var(--blue-2);
            --c2: var(--blue-3);
            background: linear-gradient(135deg, var(--c1), var(--c2));
            color: white;
            border: none;
            border-radius: 14px;
            padding: 0.75rem 1.1rem;
            box-shadow: 0 8px 22px rgba(30, 136, 229, 0.35);
            transition: transform 0.15s ease, box-shadow 0.3s ease, filter 0.3s ease;
        }

        .btn-pulse:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
        }

        .btn-pulse:focus {
            box-shadow: var(--shadow-focus);
            outline: none;
        }

        .btn-ghost {
            color: var(--blue-1);
            border: 1px solid rgba(100, 181, 246, 0.35);
            background: rgba(255, 255, 255, 0.03);
            border-radius: 14px;
            transition: 0.25s ease;
        }

        .btn-ghost:hover {
            background: rgba(100, 181, 246, 0.1);
            color: white;
        }

        /* Auth container */
        .auth-container {
            display: flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .auth-card {
            background: linear-gradient(180deg,
                    rgba(255, 255, 255, 0.05),
                    rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow-1);
            width: 100%;
            max-width: 450px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: rgba(30, 136, 229, 0.15);
            margin-bottom: 1.5rem;
            color: var(--blue-1);
            font-size: 2rem;
        }

        .auth-title {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .auth-subtitle {
            color: var(--text-2);
            margin-bottom: 0;
        }

        .auth-form .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
        }

        .auth-form .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--blue-1);
            color: white;
            box-shadow: var(--shadow-focus);
        }

        .auth-form .form-label {
            color: var(--text-2);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .auth-form .input-group-text {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-2);
            border-radius: var(--radius-md);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--text-2);
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .divider-text {
            padding: 0 1rem;
            font-size: 0.9rem;
        }

        .social-login {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-md);
            color: var(--text-1);
            transition: all 0.2s ease;
        }

        .social-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-2px);
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-2);
        }

        .auth-link {
            color: var(--blue-1);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .auth-link:hover {
            color: var(--blue-2);
            text-decoration: underline;
        }

        .password-toggle {
            cursor: pointer;
            color: var(--text-2);
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--blue-1);
        }

        /* Footer */
        footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.25);
            margin-top: auto;
        }

        /* Toast notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .auth-card {
                padding: 2rem 1.5rem;
            }

            .social-login {
                grid-template-columns: 1fr;
            }
        }

        /* Tabs for switching between forms */
        .auth-tabs {
            display: flex;
            background: rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-md);
            padding: 0.25rem;
            margin-bottom: 2rem;
        }

        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .auth-tab.active {
            background: rgba(30, 136, 229, 0.2);
            color: var(--blue-1);
            font-weight: 500;
        }
    </style>
    <?php $this->content('styles'); ?>
</head>

<body>
    <?php echo $this->escape(display_flash_message()); ?>

    <?php $this->content('content'); ?>

    <script src="/dist/js/jquery-3.7.1.min.js"></script>
    <script src="/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Year in footer
        // document.getElementById("y").textContent = new Date().getFullYear();

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
        setupPasswordToggle('signupPasswordToggle', 'signupPassword');
        setupPasswordToggle('confirmPasswordToggle', 'confirmPassword');

        // Switch between login and signup forms
        const loginForm = document.getElementById('loginForm');
        const signupForm = document.getElementById('signupForm');
        const loginTab = document.getElementById('loginTab');
        const signupTab = document.getElementById('signupTab');
        const switchToSignup = document.getElementById('switchToSignup');
        const switchToLogin = document.getElementById('switchToLogin');

        function showLoginForm() {
            loginForm.classList.remove('d-none');
            signupForm.classList.add('d-none');
            loginTab.classList.add('active');
            signupTab.classList.remove('active');
        }

        function showSignupForm() {
            loginForm.classList.add('d-none');
            signupForm.classList.remove('d-none');
            loginTab.classList.remove('active');
            signupTab.classList.add('active');
        }

        // Add event listeners
        loginTab.addEventListener('click', showLoginForm);
        signupTab.addEventListener('click', showSignupForm);
        switchToSignup.addEventListener('click', function(e) {
            e.preventDefault();
            showSignupForm();
        });
        switchToLogin.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginForm();
        });

        // Form submission
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real application, you would validate and submit the form
            simulateLogin();
        });

        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // In a real application, you would validate and submit the form
            simulateSignup();
        });

        // Simulate form submission
        function simulateLogin() {
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...';

            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;

                // Show success message
                showToast('Successfully signed in! Redirecting...', 'success');

                // Redirect after a delay
                setTimeout(() => {
                    window.location.href = 'index.html'; // Redirect to homepage
                }, 1500);
            }, 1500);
        }

        function simulateSignup() {
            const submitBtn = signupForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating account...';

            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;

                // Show success message
                showToast('Account created successfully! Redirecting...', 'success');

                // Redirect after a delay
                setTimeout(() => {
                    window.location.href = 'index.html'; // Redirect to homepage
                }, 1500);
            }, 1500);
        }

        // Show toast notification
        function showToast(message, type = 'info') {
            // Remove any existing toasts
            const existingToasts = document.querySelectorAll('.toast');
            existingToasts.forEach(toast => toast.remove());

            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill'} me-2"></i> ${message}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      `;

            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', function() {
                document.body.removeChild(toast);
            });
        }

        // Nav button functionality
        document.getElementById('loginNavBtn').addEventListener('click', function(e) {
            e.preventDefault();
            showLoginForm();
            window.scrollTo(0, 0);
        });

        document.getElementById('signupNavBtn').addEventListener('click', function(e) {
            e.preventDefault();
            showSignupForm();
            window.scrollTo(0, 0);
        });

        document.getElementById('mobileLoginBtn').addEventListener('click', function(e) {
            e.preventDefault();
            showLoginForm();
        });

        document.getElementById('mobileSignupBtn').addEventListener('click', function(e) {
            e.preventDefault();
            showSignupForm();
        });
    </script>
    <?php $this->content('scripts'); ?>
</body>

</html>