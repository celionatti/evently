<?php

declare(strict_types=1);

namespace Trees\Helper\Csrf;

use Exception;
use RuntimeException;

/**
 * =========================================
 * *****************************************
 * =========== Tress CSRF Class ============
 * *****************************************
 * CSRF Token Generator and Validator
 *
 * Provides secure generation and validation of
 * CSRF tokens with configurable expiration.
 * =========================================
 */

class Csrf
{
    private const DEFAULT_SESSION_KEY = '__trees_csrf_token';
    private const DEFAULT_EXPIRY_KEY = '__trees_csrf_token_expiry';
    private const DEFAULT_LIFETIME = 3600; // 1 hour in seconds

    private string $sessionKey;
    private string $sessionExpiryKey;
    private int $tokenLifetime;
    private bool $autoStartSession;

    /**
     * Constructor
     *
     * @param int $tokenLifetime Token lifetime in seconds
     * @param string|null $sessionKey Custom session key for the token
     * @param string|null $expiryKey Custom session key for the expiry time
     * @param bool $autoStartSession Whether to automatically start sessions
     */
    public function __construct(
        int $tokenLifetime = self::DEFAULT_LIFETIME,
        ?string $sessionKey = null,
        ?string $expiryKey = null,
        bool $autoStartSession = true
    ) {
        $this->tokenLifetime = $tokenLifetime;
        $this->sessionKey = $sessionKey ?? self::DEFAULT_SESSION_KEY;
        $this->sessionExpiryKey = $expiryKey ?? self::DEFAULT_EXPIRY_KEY;
        $this->autoStartSession = $autoStartSession;
    }

    /**
     * Generate a new CSRF token
     *
     * @return string The generated token
     * @throws RuntimeException If token generation fails
     */
    public function generateToken(): string
    {
        $this->ensureSessionStarted();

        try {
            $token = bin2hex(random_bytes(32));
            $_SESSION[$this->sessionKey] = $token;
            $_SESSION[$this->sessionExpiryKey] = time() + $this->tokenLifetime;

            return $token;
        } catch (Exception $e) {
            throw new RuntimeException('CSRF token generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the current CSRF token
     *
     * @return string|null The current token or null if none exists
     */
    public function getToken(): ?string
    {
        $this->ensureSessionStarted();

        if (!$this->hasValidToken()) {
            return null;
        }

        return $_SESSION[$this->sessionKey];
    }

    /**
     * Validate a token against the stored token
     *
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public function validateToken(string $token): bool
    {
        $this->ensureSessionStarted();

        if (!$this->hasValidToken()) {
            return false;
        }

        return hash_equals($_SESSION[$this->sessionKey], $token);
    }

    /**
     * Check if a valid token exists (not expired)
     *
     * @return bool True if a valid token exists
     */
    public function hasValidToken(): bool
    {
        $this->ensureSessionStarted();

        if (empty($_SESSION[$this->sessionKey]) || empty($_SESSION[$this->sessionExpiryKey])) {
            return false;
        }

        if (time() > $_SESSION[$this->sessionExpiryKey]) {
            $this->clearToken();
            return false;
        }

        return true;
    }

    /**
     * Clear the current CSRF token
     */
    public function clearToken(): void
    {
        $this->ensureSessionStarted();
        unset($_SESSION[$this->sessionKey], $_SESSION[$this->sessionExpiryKey]);
    }

    /**
     * Get the remaining lifetime of the current token
     *
     * @return int|null Remaining seconds or null if no valid token
     */
    public function getTokenLifetimeRemaining(): ?int
    {
        $this->ensureSessionStarted();

        if (!$this->hasValidToken()) {
            return null;
        }

        return $_SESSION[$this->sessionExpiryKey] - time();
    }

    /**
     * Ensure a session is started if auto-start is enabled
     *
     * @throws RuntimeException If session cannot be started
     */
    private function ensureSessionStarted(): void
    {
        if (!$this->autoStartSession) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            if (!session_start()) {
                throw new RuntimeException('Failed to start session for CSRF token');
            }
        }
    }
}