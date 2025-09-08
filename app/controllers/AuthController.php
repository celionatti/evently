<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\User;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Controller\Controller;
use Trees\Exception\TreesException;
use Trees\Helper\FlashMessages\FlashMessage;

class AuthController extends Controller
{
    private ?User $userModel;
    private const MAX_LOGIN_ATTEMPTS = 3;
    private const BLOCK_DURATION_MINUTES = 30; // Block for 30 minutes after max attempts
    private const REMEMBER_TOKEN_DAYS = 30;

    public function onConstruct()
    {
        $this->view->setLayout('auth');
        $name = "Eventlyy";
        $this->view->setTitle("Authentication | {$name}");
        $this->userModel = new User();

        // Check remember me token on every request
        $this->checkRememberMeToken();
    }

    public function login(Request $request, Response $response)
    {
        // Check if user is already logged in
        if ($this->isLoggedIn()) {
            return $response->redirect("/");
        }

        $view = [];
        return $this->render('auth/login', $view);
    }

    public function login_user(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            $response->redirect('/login');
            return;
        }

        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            $response->redirect('/login');
            return;
        }

        try {
            $email = strtolower(trim($request->input('email')));
            $password = $request->input('password');
            $remember = $request->input('remember', false) ? true : false;

            // Find user by email
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                FlashMessage::setMessage('Invalid email or password', 'danger');
                set_form_data($request->except(['password']));
                $response->redirect('/login');
                return;
            }

            // Check if user is temporarily blocked due to failed attempts
            if ($this->isTemporarilyBlocked($user)) {
                $blockedUntil = new \DateTime($user->blocked_until);
                $timeRemaining = $blockedUntil->diff(new \DateTime())->format('%i minutes');
                FlashMessage::setMessage("Account temporarily blocked due to multiple failed login attempts. Try again in {$timeRemaining}.", 'danger');
                $response->redirect('/login');
                return;
            }

            // Check if user is permanently blocked
            if ($user->isBlocked()) {
                FlashMessage::setMessage('Your account has been blocked. Please contact support.', 'danger');
                $response->redirect('/login');
                return;
            }

            // Verify password
            if (!$user->verifyPassword($password)) {
                $this->handleFailedLogin($user);
                FlashMessage::setMessage('Invalid email or password', 'danger');
                set_form_data($request->except(['password']));
                $response->redirect('/login');
                return;
            }

            // Successful login - reset login attempts
            $this->resetLoginAttempts($user);

            // Login user
            $this->loginUser($user, $remember);

            FlashMessage::setMessage('Welcome back!', 'success');

            return $response->redirect('/');
        } catch (TreesException $e) {
            FlashMessage::setMessage('Login failed. Please try again.', 'danger');
            set_form_data($request->except(['password']));
            $response->redirect('/login');
        }
    }

    public function signup(Request $request, Response $response)
    {
        // Check if user is already logged in
        if ($this->isLoggedIn()) {
            return $response->redirect("/");
        }

        $view = [];
        return $this->render('auth/signup', $view);
    }

    public function create_user(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            $response->redirect('/sign-up');
            return;
        }

        $rules = [
            'name' => 'required|min:2|max:50',
            'other_name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:users.email',
            'password' => 'required|min:8|password_secure',
            'password_confirmation' => 'required|same:password',
            'terms' => 'required|accepted'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            $response->redirect('/sign-up');
            return;
        }

        try {
            // Generate unique user ID
            $userId = User::generateUserId(
                $request->input('name'),
                $request->input('other_name')
            );

            // Hash password
            $hashedPassword = password_hash($request->input('password'), PASSWORD_DEFAULT);

            // Prepare user data
            $userData = [
                'user_id' => $userId,
                'name' => trim($request->input('name')),
                'other_name' => trim($request->input('other_name')),
                'email' => strtolower(trim($request->input('email'))),
                'password' => $hashedPassword,
                'role' => 'organiser',
                'is_blocked' => 0,
                'login_attempts' => 0,
                'blocked_until' => null,
                'last_login_attempt' => null
            ];

            // Create user
            $user = $this->userModel->create($userData);

            if ($user) {
                FlashMessage::setMessage('Registration successful! Please login to continue.', 'success');
                $response->redirect('/login');
                return;
            }

            throw new \Exception('Failed to create user account');
        } catch (TreesException $e) {
            FlashMessage::setMessage('Registration failed. Please try again.', 'danger');
            set_form_data($request->all());
            $response->redirect('/sign-up');
        } catch (\Exception $e) {
            FlashMessage::setMessage('An unexpected error occurred. Please try again.', 'danger');
            set_form_data($request->all());
            $response->redirect('/sign-up');
        }
    }

    public function logout(Request $request, Response $response)
    {
        $this->logoutUser();
        FlashMessage::setMessage('You have been logged out successfully.', 'success');
        $response->redirect('/');
    }

    /**
     * Handle failed login attempts
     */
    private function handleFailedLogin(User $user): void
    {
        $attempts = ($user->login_attempts ?? 0) + 1;
        $now = new \DateTime();

        $updateData = [
            'login_attempts' => $attempts,
            'last_login_attempt' => $now->format('Y-m-d H:i:s')
        ];

        // Block user temporarily if max attempts reached
        if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
            $blockedUntil = clone $now;
            $blockedUntil->add(new \DateInterval('PT' . self::BLOCK_DURATION_MINUTES . 'M'));

            $updateData['blocked_until'] = $blockedUntil->format('Y-m-d H:i:s');
        }

        // Use the static updateWhere method to avoid return type issues
        User::updateWhere(['email' => $user->email], $updateData);
    }

    /**
     * Reset login attempts after successful login
     */
    private function resetLoginAttempts(User $user): void
    {
        // Use the static updateWhere method to avoid return type issues
        User::updateWhere(['email' => $user->email], [
            'login_attempts' => 0,
            'blocked_until' => null,
            'last_login_attempt' => null
        ]);
    }

    /**
     * Check if user is temporarily blocked
     */
    private function isTemporarilyBlocked(User $user): bool
    {
        if (!$user->blocked_until) {
            return false;
        }

        $blockedUntil = new \DateTime($user->blocked_until);
        $now = new \DateTime();

        // If block period has expired, unblock the user
        if ($now >= $blockedUntil) {
            $user->update([
                'login_attempts' => 0,
                'blocked_until' => null,
                'last_login_attempt' => null
            ]);
            return false;
        }

        return true;
    }

    /**
     * Check remember me token and auto-login if valid
     */
    private function checkRememberMeToken(): void
    {
        // Skip if already logged in
        if ($this->isLoggedIn()) {
            return;
        }

        // Check if remember token exists in cookie
        if (!isset($_COOKIE['remember_token']) || empty($_COOKIE['remember_token'])) {
            return;
        }

        $token = $_COOKIE['remember_token'];

        // Find user by remember token
        $user = $this->userModel->first(['remember_token' => $token]);

        if (!$user) {
            // Invalid token, clear cookie
            $this->clearRememberCookie();
            return;
        }

        // Check if user is blocked
        if ($user->isBlocked() || $this->isTemporarilyBlocked($user)) {
            $this->clearRememberCookie();
            return;
        }

        // Regenerate session ID for auto-login as well
    session()->regenerate(true);

        // Auto-login the user
        $this->loginUser($user, true);
    }

    /**
     * Login user and set session
     */
    private function loginUser(User $user, bool $remember = false): void
    {
        // Regenerate session ID to prevent session fixation attacks
        session()->regenerate(true);

        // Set session data
        session()->set('user_id', $user->user_id);
        session()->set('user_email', $user->email);
        session()->set('user_name', $user->name);
        session()->set('user_role', $user->role);
        session()->set('logged_in', true);

        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (self::REMEMBER_TOKEN_DAYS * 24 * 60 * 60);

            // Store remember token in database
            $user->update(['remember_token' => $token]);

            setcookie('remember_token', $token, $expiry, '/', '', true, true);
        }
    }

    /**
     * Logout user and clear session
     */
    private function logoutUser(): void
    {
        // Get current user to clear remember token from database
        $userId = session()->get('user_id');
        if ($userId) {
            $user = $this->userModel->findByUserId($userId);
            if ($user) {
                $user->update(['remember_token' => null]);
            }
        }

        // Clear session data
        session()->remove('user_id');
        session()->remove('user_email');
        session()->remove('user_name');
        session()->remove('user_role');
        session()->remove('logged_in');

        // Clear remember me cookie
        $this->clearRememberCookie();

        // Destroy session
        session()->destroy();
    }

    /**
     * Clear remember me cookie
     */
    private function clearRememberCookie(): void
    {
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
    }

    /**
     * Check if user is logged in
     */
    private function isLoggedIn(): bool
    {
        return session()->get('logged_in', false) === true;
    }

    /**
     * Get current logged in user
     */
    public function getCurrentUser(): ?User
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userId = session()->get('user_id');
        return $userId ? $this->userModel->findByUserId($userId) : null;
    }

    /**
     * Require authentication middleware
     */
    public function requireAuth(Request $request, Response $response): bool
    {
        if (!$this->isLoggedIn()) {
            FlashMessage::setMessage('Please login to access this page.', 'warning');
            $response->redirect('/login');
            return false;
        }
        return true;
    }

    /**
     * Check if current user has specific role
     */
    public function hasRole(string $role): bool
    {
        $userRole = session()->get('user_role');
        return $userRole === $role;
    }

    /**
     * Require specific role
     */
    public function requireRole(string $role, Request $request, Response $response): bool
    {
        if (!$this->requireAuth($request, $response)) {
            return false;
        }

        if (!$this->hasRole($role)) {
            FlashMessage::setMessage('Access denied. Insufficient permissions.', 'danger');
            $response->redirect('/');
            return false;
        }

        return true;
    }
}
