<?php

declare(strict_types=1);

namespace Trees\Session;

/**
 * ========================================
 * ****************************************
 * ========= SessionInterface Class =======
 * ****************************************
 * SessionInterface - Defines the contract for session management
 *
 * Provides methods for handling session data, flash messages,
 * CSRF tokens, and session expiration.
 * ========================================
 */

interface SessionInterface
{
    /**
     * Starts the session with optional configuration
     *
     * @param array $options Session configuration options
     */
    public function start(array $options = []): void;

    /**
     * Checks if the session has been started
     */
    public function isStarted(): bool;

    /**
     * Sets a session value
     *
     * @param string $key The session key
     * @param mixed $value The value to store
     */
    public function set(string $key, $value): void;

    /**
     * Retrieves a session value
     *
     * @param string $key The session key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The stored value or default
     */
    public function get(string $key, $default = null);

    /**
     * Checks if a session key exists
     */
    public function has(string $key): bool;

    /**
     * Removes a session value
     */
    public function remove(string $key): void;

    /**
     * Destroys the session and removes all data
     */
    public function destroy(): void;

    /**
     * Regenerates the session ID
     *
     * @param bool $deleteOldSession Whether to delete the old session data
     */
    public function regenerate(bool $deleteOldSession = true): void;

    /**
     * Sets multiple session values at once
     */
    public function setArray(array $data): void;

    /**
     * Removes multiple session values at once
     */
    public function unsetArray(array $keys): void;

    /**
     * Sets a flash message that will be available on next request only
     */
    public function flash(string $key, $value): void;

    /**
     * Retrieves a flash message
     *
     * @return mixed The flash message or default value
     */
    public function getFlash(string $key, $default = null);

    /**
     * Checks if a flash message exists
     */
    public function hasFlash(string $key): bool;

    /**
     * Keeps a flash message for one additional request
     */
    public function keepFlash(string $key): void;

    /**
     * Sets a form message (typically used for form validation errors)
     */
    public function setFormMessage($value): void;

    /**
     * Retrieves and removes the form message
     *
     * @return array The form message (always returns array for consistency)
     */
    public function getFormMessage(): array;

    /**
     * Sets session expiration time
     *
     * @param int $minutes Minutes until session expires
     */
    public function setExpiration(int $minutes): void;

    /**
     * Checks if session has expired and destroys it if needed
     */
    public function checkExpiration(): void;

    /**
     * Generates a new CSRF token and stores it in session
     *
     * @return string The generated token
     */
    public function generateCsrfToken(): string;

    /**
     * Validates a CSRF token against the stored value
     *
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public function validateCsrfToken(string $token): bool;
}