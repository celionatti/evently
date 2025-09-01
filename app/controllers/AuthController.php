<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\User;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Mailer\MailService;
use Trees\Controller\Controller;
use Trees\Exception\TreesException;
use Trees\Security\SecurityService;
use Trees\Helper\FlashMessages\FlashMessage;

class AuthController extends Controller
{
    private ?User $userModel;
    private ?SecurityService $securityService;

    public function onConstruct()
    {
        $this->view->setLayout('auth');
        $name = "Eventlyy";
        $this->view->setTitle("Authentication | {$name}");
        $this->userModel = new User();
        $this->securityService = new SecurityService();
    }

    public function login()
    {
        $view = [];
        return $this->render('auth/login', $view);
    }

    public function signup()
    {
        $view = [];
        return $this->render('auth/signup', $view);
    }

    public function create_user(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $rules = [
            'name' => 'required|min:3',
            'other_name' => 'required|min:3',
            'email' => 'required|email|unique:users.email',
            'password' => 'required|password_secure',
            'password_confirmation' => 'required',
            'terms' => 'required'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            $response->redirect("/sign-up");
            return;
        }

        // Generate unique user ID
        $userId = 'USR_' . uniqid() . '_' . str_slug($request->input('name') . ' ' . $request->input('other_name'));
        
        // Hash password
        $hashedPassword = password_hash($request->input('password'), PASSWORD_DEFAULT);
        
        // Generate verification token
        $verificationToken = bin2hex(random_bytes(32));
        $tokenExpire = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Prepare user data
        $userData = [
            'user_id' => $userId,
            'name' => $request->input('name'),
            'other_name' => $request->input('other_name'),
            'email' => $request->input('email'),
            'password' => $hashedPassword,
            'role' => 'organiser',
            'token' => $verificationToken,
            'token_expire' => $tokenExpire,
            'security_setup_completed' => 0, // Initialize security setup as incomplete
        ];

        try {
            // Create user
            $user = $this->userModel->create($userData);
            
            if ($user) {
                // Start security setup process
                $securitySetup = $this->startSecuritySetup($userId);
                
                if ($securitySetup['success']) {
                    // Store security session token in session for later use
                    $_SESSION['security_session_token'] = $securitySetup['session_token'];
                    $_SESSION['recovery_phrase'] = $securitySetup['recovery_phrase'];
                    $_SESSION['new_user_id'] = $userId;
                    
                    FlashMessage::setMessage('Registration successful! Please complete your security setup.', 'success');
                    $response->redirect("/security-setup");
                    return;
                } else {
                    FlashMessage::setMessage('Registration successful, but security setup failed. Please contact support.', 'warning');
                    $response->redirect("/secure-verifcation/pin/{$verificationToken}");
                    return;
                }
            }

            throw new \Exception('Failed to create user account');
           
        } catch (TreesException $e) {
            FlashMessage::setMessage('Registration failed. Please try again.', 'danger');
            set_form_data($request->all());
            $response->redirect("/sign-up");
        }
    }

    /**
     * Show security setup page
     */
    public function securitySetup(Request $request, Response $response)
    {
        // Check if we have the required session data
        if (!isset($_SESSION['security_session_token']) || !isset($_SESSION['recovery_phrase'])) {
            FlashMessage::setMessage('Invalid security setup session. Please register again.', 'danger');
            return $response->redirect('/sign-up');
        }

        $view = [
            'recovery_phrase' => $_SESSION['recovery_phrase'],
            'session_token' => $_SESSION['security_session_token']
        ];

        return $this->render('auth/security-setup', $view);
    }

    /**
     * Process security setup completion
     */
    public function completeSecuritySetup(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $rules = [
            'pin' => 'required|min:4|max:8|numeric',
            'pin_confirmation' => 'required|same:pin',
            'recovery_phrase_confirmed' => 'required'
        ];

        if (!$request->validate($rules, false)) {
            set_form_error($request->getErrors());
            $response->redirect("/security-setup");
            return;
        }

        $sessionToken = $_SESSION['security_session_token'] ?? '';
        $userId = $_SESSION['new_user_id'] ?? '';
        $recoveryPhrase = $_SESSION['recovery_phrase'] ?? '';
        $pin = $request->input('pin');

        if (empty($sessionToken) || empty($userId) || empty($recoveryPhrase)) {
            FlashMessage::setMessage('Invalid security setup session. Please try again.', 'danger');
            $response->redirect('/sign-up');
            return;
        }

        try {
            $completed = $this->securityService->completeSecuritySetup(
                $sessionToken,
                $userId,
                $pin,
                $recoveryPhrase
            );

            if ($completed) {
                // Clear security setup session data
                unset($_SESSION['security_session_token']);
                unset($_SESSION['recovery_phrase']);
                unset($_SESSION['new_user_id']);

                FlashMessage::setMessage('Security setup completed successfully! You can now login.', 'success');
                $response->redirect('/login');
                return;
            }

            FlashMessage::setMessage('Failed to complete security setup. Please try again.', 'danger');
            $response->redirect('/security-setup');

        } catch (\Exception $e) {
            FlashMessage::setMessage('Security setup failed: ' . $e->getMessage(), 'danger');
            $response->redirect('/security-setup');
        }
    }

    /**
     * Show PIN verification page (for login or sensitive operations)
     */
    public function verifyPin(Request $request, Response $response)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            FlashMessage::setMessage('Please login first.', 'warning');
            return $response->redirect('/login');
        }

        $view = [];
        return $this->render('auth/verify-pin', $view);
    }

    /**
     * Process PIN verification
     */
    public function processPinVerification(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $rules = [
            'pin' => 'required|numeric'
        ];

        if (!$request->validate($rules, false)) {
            set_form_error($request->getErrors());
            $response->redirect("/verify-pin");
            return;
        }

        $userId = $_SESSION['user_id'] ?? '';
        $pin = $request->input('pin');

        if (empty($userId)) {
            FlashMessage::setMessage('Please login first.', 'warning');
            $response->redirect('/login');
            return;
        }

        try {
            $isValid = $this->securityService->verifyUserPin($userId, $pin);

            if ($isValid) {
                $_SESSION['pin_verified'] = true;
                $_SESSION['pin_verified_at'] = time();
                
                // Redirect to intended destination or dashboard
                $redirectTo = $_SESSION['intended_url'] ?? '/dashboard';
                unset($_SESSION['intended_url']);
                
                FlashMessage::setMessage('PIN verified successfully.', 'success');
                $response->redirect($redirectTo);
                return;
            }

            FlashMessage::setMessage('Invalid PIN. Please try again.', 'danger');
            $response->redirect('/verify-pin');

        } catch (\Exception $e) {
            FlashMessage::setMessage('PIN verification failed. Please try again.', 'danger');
            $response->redirect('/verify-pin');
        }
    }

    /**
     * Show PIN reset page
     */
    public function resetPin()
    {
        $view = [];
        return $this->render('auth/reset-pin', $view);
    }

    /**
     * Process PIN reset with recovery phrase
     */
    public function processResetPin(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $rules = [
            'email' => 'required|email',
            'recovery_phrase' => 'required',
            'new_pin' => 'required|min:4|max:8|numeric',
            'new_pin_confirmation' => 'required|same:new_pin'
        ];

        if (!$request->validate($rules, false)) {
            set_form_error($request->getErrors());
            $response->redirect("/reset-pin");
            return;
        }

        try {
            // Find user by email
            $user = User::findByEmail($request->input('email'));
            if (!$user) {
                FlashMessage::setMessage('User not found.', 'danger');
                $response->redirect('/reset-pin');
                return;
            }

            $reset = $this->securityService->resetPinWithRecoveryPhrase(
                $user->user_id,
                $request->input('recovery_phrase'),
                $request->input('new_pin')
            );

            if ($reset) {
                FlashMessage::setMessage('PIN reset successfully. You can now login with your new PIN.', 'success');
                $response->redirect('/login');
                return;
            }

            FlashMessage::setMessage('Invalid recovery phrase or reset failed.', 'danger');
            $response->redirect('/reset-pin');

        } catch (\Exception $e) {
            FlashMessage::setMessage('PIN reset failed: ' . $e->getMessage(), 'danger');
            $response->redirect('/reset-pin');
        }
    }

    /**
     * Helper method to start security setup
     */
    private function startSecuritySetup(string $userId): array
    {
        try {
            $sessionToken = $this->securityService->createSecuritySession($userId);
            $recoveryPhrase = $this->securityService->generateRecoveryPhrase();

            return [
                'success' => true,
                'session_token' => $sessionToken,
                'recovery_phrase' => $recoveryPhrase
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if current user has completed security setup
     */
    public function requiresSecuritySetup(): bool
    {
        $userId = $_SESSION['user_id'] ?? '';
        if (empty($userId)) {
            return false;
        }

        return !$this->securityService->hasUserCompletedSetup($userId);
    }

    /**
     * Check if PIN verification is required and valid
     */
    public function requiresPinVerification(): bool
    {
        // Check if PIN was verified recently (within 30 minutes)
        $pinVerified = $_SESSION['pin_verified'] ?? false;
        $verifiedAt = $_SESSION['pin_verified_at'] ?? 0;
        
        if ($pinVerified && (time() - $verifiedAt) < 1800) { // 30 minutes
            return false;
        }

        // Clear expired PIN verification
        unset($_SESSION['pin_verified']);
        unset($_SESSION['pin_verified_at']);
        
        return true;
    }
}