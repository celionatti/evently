<?php

declare(strict_types=1);

use App\models\User;
use Trees\Helper\FlashMessages\FlashMessage;

/**
 * Get authenticated user data from session and database
 */
function auth(): ?User
{
    static $user = null;
    
    // Return cached user if already fetched in this request
    if ($user !== null) {
        return $user;
    }
    
    // Check if user is logged in via session
    if (!session()->has('user_id') || !session()->get('logged_in', false)) {
        return null;
    }
    
    $userId = session()->get('user_id');
    if (!$userId) {
        return null;
    }
    
    try {
        $userModel = new User();
        $user = $userModel->findByUserId($userId);
        
        if (!$user) {
            // User doesn't exist in database but session exists - clear invalid session
            clearAuthSession();
            return null;
        }
        
        // Check if user is blocked or temporarily blocked
        if (isUserBlocked($user)) {
            clearAuthSession();
            clearRememberCookie();
            return null;
        }
        
        return $user;
        
    } catch (\Exception $e) {
        error_log("Auth helper error: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if user is blocked or temporarily blocked
 */
function isUserBlocked(User $user): bool
{
    // Check permanent block
    if ($user->isBlocked()) {
        return true;
    }
    
    // Check temporary block
    if ($user->blocked_until) {
        $blockedUntil = new DateTime($user->blocked_until);
        $now = new DateTime();
        
        // If still blocked, return true
        if ($now < $blockedUntil) {
            return true;
        }
        
        // Block has expired, reset attempts
        $userModel = new User();
        $userModel->updateWhere(
            ['user_id' => $user->user_id],
            [
                'login_attempts' => 0,
                'blocked_until' => null,
                'last_login_attempt' => null
            ]
        );
    }
    
    return false;
}

/**
 * Clear authentication session
 */
function clearAuthSession(): void
{
    session()->remove('user_id');
    session()->remove('user_email');
    session()->remove('user_name');
    session()->remove('user_role');
    session()->remove('logged_in');
}

/**
 * Clear remember me cookie
 */
function clearRememberCookie(): void
{
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        unset($_COOKIE['remember_token']);
    }
}

/**
 * Check if user is authenticated
 */
function isAuthenticated(): bool
{
    return auth() !== null;
}

/**
 * Check if authenticated user has specific role
 */
function hasRole(string $role): bool
{
    $user = auth();
    return $user && $user->role === $role;
}

/**
 * Get current user ID
 */
function userId(): ?string
{
    $user = auth();
    return $user ? $user->user_id : null;
}

/**
 * Require authentication - use as middleware
 */
function requireAuth(): void
{
    if (!isAuthenticated()) {
        FlashMessage::setMessage('Please login to access this page.', 'warning');
        redirect('/login');
        exit;
    }
}

/**
 * Require specific role
 */
function requireRole(string $role): void
{
    requireAuth();
    
    if (!hasRole($role)) {
        FlashMessage::setMessage('Access denied. Insufficient permissions.', 'danger');
        redirect('/');
        exit;
    }
}

/**
 * Simple redirect helper (you might already have this)
 */
// function redirect(string $url): void
// {
//     header("Location: $url");
//     exit;
// }