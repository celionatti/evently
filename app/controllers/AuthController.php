<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\User;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Mailer\MailService;
use Trees\Controller\Controller;
use Trees\Exception\TreesException;
use Trees\Helper\FlashMessages\FlashMessage;

class AuthController extends Controller
{
    private ?User $userModel;
    private ?MailService $mailService;

    public function onConstruct()
    {
        $this->view->setLayout('auth');
        $name = "Eventlyy";
        $this->view->setTitle("Authentication | {$name}");
        $this->userModel = new User();
        $this->mailService = new MailService();
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
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            // Create user
            $user = $this->userModel->create($userData);

            if ($user) {
                // Send verification email
                $this->sendVerificationEmail($user->email, $user->name, $verificationToken);

                // Set success message
                FlashMessage::setMessage('Registration successful! Please check your email to verify your account.', 'success');

                $response->redirect("/login");
                return;
            }

            throw new \Exception('Failed to create user account');
        } catch (TreesException $e) {
            // Log error
            error_log("Registration error: " . $e->getMessage());

            FlashMessage::setMessage('Registration failed. Please try again.', 'danger');
            set_form_data($request->all());
            $response->redirect("/sign-up");
        }
    }

    public function verify_email(Request $request, Response $response)
    {
        $token = $request->input('token');

        if (empty($token)) {
            FlashMessage::setMessage('Invalid verification link.', 'danger');
            $response->redirect("/login");
            return;
        }

        // Find user by token
        $user = $this->userModel->where(['token' => $token])[0] ?? null;

        if (!$user) {
            FlashMessage::setMessage('Invalid verification token.', 'danger');
            $response->redirect("/login");
            return;
        }

        // Check if token is expired
        if (strtotime($user->token_expire) < time()) {
            FlashMessage::setMessage('Verification link has expired.', 'danger');
            $response->redirect("/login");
            return;
        }

        // Update user (clear token and mark as verified)
        $updateData = [
            'token' => null,
            'token_expire' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->userModel->updateWhere(['id' => $user->id], $updateData)) {
            FlashMessage::setMessage('Email verified successfully! You can now login.', 'success');
        } else {
            FlashMessage::setMessage('Failed to verify email. Please try again.', 'danger');
        }

        $response->redirect("/login");
    }

    private function sendVerificationEmail(string $email, string $name, string $token): bool
    {
        $verificationUrl = url("/verify-email?token={$token}");

        $subject = "Verify Your Email - Eventlyy";

        $body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Email Verification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .button { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
                .footer { margin-top: 20px; padding: 20px; background: #f0f0f0; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to Eventlyy!</h1>
                </div>
                <div class='content'>
                    <h2>Hi {$name},</h2>
                    <p>Thank you for registering with Eventlyy. Please verify your email address to complete your registration.</p>
                    <p>
                        <a href='{$verificationUrl}' class='button'>Verify Email Address</a>
                    </p>
                    <p>Or copy and paste this link in your browser:</p>
                    <p>{$verificationUrl}</p>
                    <p>This verification link will expire in 24 hours.</p>
                </div>
                <div class='footer'>
                    <p>If you did not create an account with Eventlyy, please ignore this email.</p>
                    <p>&copy; " . date('Y') . " Eventlyy. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        return $this->mailService->sendEmail($email, $subject, $body);
    }
}
