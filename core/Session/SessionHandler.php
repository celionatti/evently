<?php

declare(strict_types=1);

namespace Trees\Session;

use Trees\Session\SessionInterface;
use RuntimeException;
use SessionHandlerInterface;

/**
 * ========================================
 * ****************************************
 * ========= SessionHandler Class =========
 * ****************************************
 * Handles session management
 * with flash messages, form messages,
 * and expiration
 * ========================================
 */

class SessionHandler implements SessionInterface
{
    protected string $flashKey = '_trees_flash';
    protected string $formMessageKey = '__trees_form_message';
    protected string $csrfTokenKey = '__trees_csrf_token';
    protected string $expirationKey = '__trees_session_expiration';
    protected bool $started = false;
    protected array $defaultOptions = [];

    public function __construct(array $defaultOptions = [])
    {
        // Set default options first
        $baseDefaults = [
            'use_cookies' => 1,
            'use_only_cookies' => 1,
            'cookie_httponly' => 1,
            'cookie_samesite' => 'Lax', // Changed from 'Strict' to 'Lax' for better compatibility
            'use_strict_mode' => 1,
            'cookie_secure' => $this->isHttps(),
            'gc_maxlifetime' => 1440,
            'sid_length' => 128,
            'sid_bits_per_character' => 6,
        ];

        // Merge with provided options
        $this->defaultOptions = array_merge($baseDefaults, $defaultOptions);
    }

    public function start(array $options = []): void
    {
        if ($this->isStarted()) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            // Start new session with our secure defaults
            session_start(array_merge($this->defaultOptions, $options));
        } else {
            // Session already active - apply our security settings
            $this->applySessionSettings();
        }
        $this->started = true;
        $this->initializeSession();
    }

    protected function applySessionSettings(): void
    {
        // Apply critical security settings even to existing sessions
        if (headers_sent() === false) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                [
                    'expires' => 0,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $this->defaultOptions['cookie_secure'],
                    'httponly' => $this->defaultOptions['cookie_httponly'],
                    'samesite' => $this->defaultOptions['cookie_samesite']
                ]
            );
        }
    }

    protected function initializeSession(): void
    {
        $this->manageFlashData();
        $this->checkExpiration();

        // Generate CSRF token only if it doesn't exist (removed auto-generation)
        // This prevents creating new tokens on every request
    }

    public function isStarted(): bool
    {
        return $this->started && session_status() === PHP_SESSION_ACTIVE;
    }

    public function set(string $key, $value): void
    {
        $this->ensureSessionStarted();
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        $this->ensureSessionStarted();
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        $this->ensureSessionStarted();
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        $this->ensureSessionStarted();
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        if (!$this->isStarted()) {
            return; // Don't attempt to destroy if not started
        }

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_unset();
        session_destroy();
        $this->started = false;
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        $this->ensureSessionStarted();

        // Validate current session ID first
        if (session_id() === '') {
            throw new RuntimeException('Cannot regenerate empty session ID');
        }

        session_regenerate_id($deleteOldSession);

        // Only regenerate CSRF token if one exists (don't force creation)
        if ($this->has($this->csrfTokenKey)) {
            $this->generateCsrfToken();
        }
    }

    public function setArray(array $data): void
    {
        $this->ensureSessionStarted();
        $_SESSION = array_merge($_SESSION, $data);
    }

    public function unsetArray(array $keys): void
    {
        $this->ensureSessionStarted();
        foreach ($keys as $key) {
            unset($_SESSION[$key]);
        }
    }

    public function flash(string $key, $value): void
    {
        $this->ensureSessionStarted();
        $_SESSION[$this->flashKey][$key] = $value;
    }

    public function getFlash(string $key, $default = null)
    {
        $this->ensureSessionStarted();
        return $_SESSION[$this->flashKey][$key] ?? $default;
    }

    public function hasFlash(string $key): bool
    {
        $this->ensureSessionStarted();
        return isset($_SESSION[$this->flashKey][$key]);
    }

    public function keepFlash(string $key): void
    {
        $this->ensureSessionStarted();
        if (isset($_SESSION[$this->flashKey][$key])) {
            $_SESSION[$this->flashKey]['_keep'][$key] = $_SESSION[$this->flashKey][$key];
        }
    }

    protected function manageFlashData(): void
    {
        if (!isset($_SESSION[$this->flashKey])) {
            $_SESSION[$this->flashKey] = [];
            return;
        }

        // Remove old flash data
        foreach ($_SESSION[$this->flashKey] as $key => $value) {
            if ($key !== '_keep') {
                unset($_SESSION[$this->flashKey][$key]);
            }
        }

        // Restore kept flash data
        if (isset($_SESSION[$this->flashKey]['_keep'])) {
            $_SESSION[$this->flashKey] = array_merge(
                $_SESSION[$this->flashKey],
                $_SESSION[$this->flashKey]['_keep']
            );
            unset($_SESSION[$this->flashKey]['_keep']);
        }
    }

    public function setFormMessage($value): void
    {
        $this->set($this->formMessageKey, $value);
    }

    public function getFormMessage(): array
    {
        $value = $this->get($this->formMessageKey, []);
        $this->remove($this->formMessageKey);
        return is_array($value) ? $value : [];
    }

    public function setExpiration(int $minutes): void
    {
        $this->set($this->expirationKey, time() + ($minutes * 60));
    }

    public function checkExpiration(): void
    {
        $expirationTime = $this->get($this->expirationKey);
        if ($expirationTime !== null && time() > $expirationTime) {
            // Don't automatically restart session after expiration
            // Let the application handle this decision
            $this->destroy();
        }
    }

    public function generateCsrfToken(): string
    {
        $this->ensureSessionStarted();
        $token = bin2hex(random_bytes(32));
        $this->set($this->csrfTokenKey, $token);
        return $token;
    }

    public function getCsrfToken(): ?string
    {
        return $this->get($this->csrfTokenKey);
    }

    public function validateCsrfToken(string $token): bool
    {
        $storedToken = $this->get($this->csrfTokenKey);
        
        // Don't remove the token immediately - let the application decide
        // This prevents issues with redirects and form resubmissions
        
        if (!$storedToken || !is_string($storedToken)) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    public function consumeCsrfToken(string $token): bool
    {
        $isValid = $this->validateCsrfToken($token);
        if ($isValid) {
            $this->remove($this->csrfTokenKey); // Only remove if validation succeeds
        }
        return $isValid;
    }

    protected function isHttps(): bool
    {
        return !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
    }

    protected function ensureSessionStarted(): void
    {
        if (!$this->isStarted()) {
            throw new RuntimeException('Session has not been started. Call start() before using session methods.');
        }
    }

    /**
     * Set custom session handler
     */
    public function setSaveHandler(SessionHandlerInterface $handler): bool
    {
        return session_set_save_handler($handler, true);
    }

    /**
     * Check if session is expired without destroying it
     */
    public function isExpired(): bool
    {
        $expirationTime = $this->get($this->expirationKey);
        return $expirationTime !== null && time() > $expirationTime;
    }

    /**
     * Extend session expiration
     */
    public function extendExpiration(int $minutes): void
    {
        if ($this->has($this->expirationKey)) {
            $this->setExpiration($minutes);
        }
    }

    /**
     * Get all session data (for debugging)
     */
    public function getAllData(): array
    {
        $this->ensureSessionStarted();
        return $_SESSION;
    }
}