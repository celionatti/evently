<?php

declare(strict_types=1);

namespace Trees\Helper\Utils;

use Exception;
use RuntimeException;

/**
 * =========================================
 * *****************************************
 * ======== Trees StringGenerator Class ====
 * *****************************************
 * A robust utility class for generating various types of unique strings with:
 * - Configurable prefixes and lengths
 * - Readability formatting options
 * - Collision avoidance
 * - Multiple character sets
 *
 * Features:
 * - Thread-safe operation
 * - Multiple string generation strategies
 * - Comprehensive validation
 * - Customizable formatting
 * =========================================
 */

class StringGenerator
{
    private string $prefix;
    private int $length;
    private array $usedCodes = [];
    private string $characterSet;
    private bool $hyphenate;
    private int $hyphenInterval;
    private bool $caseSensitive;
    private static ?StringGenerator $instance = null;

    // Predefined character sets
    public const CHARSET_ALPHANUMERIC = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const CHARSET_ALPHANUMERIC_LOWER = '0123456789abcdefghijklmnopqrstuvwxyz';
    public const CHARSET_ALPHANUMERIC_MIXED = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    public const CHARSET_NUMERIC = '0123456789';
    public const CHARSET_ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const CHARSET_ALPHA_LOWER = 'abcdefghijklmnopqrstuvwxyz';
    public const CHARSET_ALPHA_MIXED = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    public const CHARSET_HEX = '0123456789ABCDEF';
    public const CHARSET_HEX_LOWER = '0123456789abcdef';
    public const CHARSET_SPECIAL = '!@#$%^&*()-_=+[]{}|;:,.<>?';

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct(
        string $prefix = '',
        int $length = 12,
        string $characterSet = self::CHARSET_ALPHANUMERIC,
        bool $hyphenate = true,
        int $hyphenInterval = 4,
        bool $caseSensitive = false
    ) {
        $this->setPrefix($prefix);
        $this->setLength($length);
        $this->setCharacterSet($characterSet);
        $this->setHyphenation($hyphenate, $hyphenInterval);
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(
        string $prefix = '',
        int $length = 12,
        string $characterSet = self::CHARSET_ALPHANUMERIC,
        bool $hyphenate = true,
        int $hyphenInterval = 4,
        bool $caseSensitive = false
    ): self {
        if (self::$instance === null) {
            self::$instance = new self(
                $prefix,
                $length,
                $characterSet,
                $hyphenate,
                $hyphenInterval,
                $caseSensitive
            );
        }
        return self::$instance;
    }

    /**
     * Generate a unique string code
     *
     * @param int|null $length Optional override of default length
     * @param string|null $prefix Optional override of default prefix
     * @return string The generated code
     * @throws RuntimeException if unable to generate unique code after max attempts
     */
    public function generateCode(?int $length = null, ?string $prefix = null): string
    {
        $maxAttempts = 100;
        $attempts = 0;
        $length = $length ?? $this->length;
        $prefix = $prefix ?? $this->prefix;

        $this->validateGenerationParameters($prefix, $length);

        do {
            $code = $this->generateRandomCode($prefix, $length);
            $attempts++;

            if ($attempts >= $maxAttempts) {
                throw new RuntimeException(
                    'Unable to generate unique code after ' . $maxAttempts . ' attempts. ' .
                    'Consider increasing length or changing character set.'
                );
            }
        } while ($this->isCodeUsed($code));

        $this->markCodeAsUsed($code);
        return $code;
    }

    /**
     * Generate a batch of unique codes
     *
     * @param int $count Number of codes to generate
     * @param int|null $length Optional length override
     * @param string|null $prefix Optional prefix override
     * @return array Array of generated codes
     * @throws RuntimeException if unable to generate required number of unique codes
     */
    public function generateBatch(int $count, ?int $length = null, ?string $prefix = null): array
    {
        if ($count < 1) {
            throw new InvalidArgumentException('Count must be at least 1');
        }

        $codes = [];
        $maxTotalAttempts = $count * 10;
        $totalAttempts = 0;

        while (count($codes) < $count && $totalAttempts < $maxTotalAttempts) {
            try {
                $codes[] = $this->generateCode($length, $prefix);
            } catch (RuntimeException $e) {
                // Ignore individual failures in batch mode
            }
            $totalAttempts++;
        }

        if (count($codes) < $count) {
            throw new RuntimeException(
                'Only generated ' . count($codes) . ' out of ' . $count . ' unique codes. ' .
                'Consider increasing length or changing character set.'
            );
        }

        return $codes;
    }

    /**
     * Generate a random code string
     */
    private function generateRandomCode(string $prefix, int $length): string
    {
        $randomPart = '';
        $charactersLength = strlen($this->characterSet);

        for ($i = 0; $i < $length; $i++) {
            $randomPart .= $this->characterSet[random_int(0, $charactersLength - 1)];
        }

        if (!$this->caseSensitive) {
            $randomPart = strtoupper($randomPart);
        }

        if ($this->hyphenate && $length > $this->hyphenInterval) {
            $randomPart = implode('-', str_split($randomPart, $this->hyphenInterval));
        }

        return $prefix ? $prefix . '-' . $randomPart : $randomPart;
    }

    /**
     * Check if a code has already been used
     */
    private function isCodeUsed(string $code): bool
    {
        $normalizedCode = $this->caseSensitive ? $code : strtoupper($code);
        return in_array($normalizedCode, $this->usedCodes, true);
    }

    /**
     * Mark a code as used
     */
    private function markCodeAsUsed(string $code): void
    {
        $normalizedCode = $this->caseSensitive ? $code : strtoupper($code);
        $this->usedCodes[] = $normalizedCode;
    }

    /**
     * Validate generation parameters
     */
    private function validateGenerationParameters(string $prefix, int $length): void
    {
        if ($length < 4) {
            throw new InvalidArgumentException('Length must be at least 4 characters');
        }

        if ($length > 128) {
            throw new InvalidArgumentException('Length must be 128 characters or less');
        }

        if (strlen($prefix) > 16) {
            throw new InvalidArgumentException('Prefix must be 16 characters or less');
        }

        if (!preg_match('/^[A-Z0-9-]*$/i', $prefix)) {
            throw new InvalidArgumentException('Prefix can only contain alphanumeric characters and hyphens');
        }

        if (empty($this->characterSet)) {
            throw new RuntimeException('Character set cannot be empty');
        }
    }

    /**
     * Set a new prefix
     *
     * @throws InvalidArgumentException if prefix is invalid
     */
    public function setPrefix(string $prefix): void
    {
        if (strlen($prefix) > 16) {
            throw new InvalidArgumentException('Prefix must be 16 characters or less');
        }

        if (!preg_match('/^[A-Z0-9-]*$/i', $prefix)) {
            throw new InvalidArgumentException('Prefix can only contain alphanumeric characters and hyphens');
        }

        $this->prefix = $prefix;
    }

    /**
     * Set the length of the random part
     *
     * @throws InvalidArgumentException if length is invalid
     */
    public function setLength(int $length): void
    {
        if ($length < 4 || $length > 128) {
            throw new InvalidArgumentException('Length must be between 4 and 128 characters');
        }
        $this->length = $length;
    }

    /**
     * Set the character set to use for generation
     *
     * @throws InvalidArgumentException if character set is empty
     */
    public function setCharacterSet(string $characterSet): void
    {
        if (empty($characterSet)) {
            throw new InvalidArgumentException('Character set cannot be empty');
        }
        $this->characterSet = $characterSet;
    }

    /**
     * Configure hyphenation settings
     *
     * @throws InvalidArgumentException if interval is invalid
     */
    public function setHyphenation(bool $hyphenate, int $interval = 4): void
    {
        if ($interval < 2 || $interval > 8) {
            throw new InvalidArgumentException('Hyphen interval must be between 2 and 8');
        }
        $this->hyphenate = $hyphenate;
        $this->hyphenInterval = $interval;
    }

    /**
     * Set case sensitivity for code checking
     */
    public function setCaseSensitive(bool $caseSensitive): void
    {
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Get array of all generated codes
     */
    public function getUsedCodes(): array
    {
        return $this->usedCodes;
    }

    /**
     * Clear all used codes (for testing or special cases)
     */
    public function clearUsedCodes(): void
    {
        $this->usedCodes = [];
    }

    /**
     * Prevent cloning of the singleton instance
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the singleton instance
     */
    public function __wakeup()
    {
        throw new RuntimeException('Cannot unserialize singleton');
    }
}