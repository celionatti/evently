<?php

declare(strict_types=1);

namespace Trees\Uuid;

/**
 * =======================================
 * ***************************************
 * ========== Trees Uuidv1 Class =========
 * UUID v1-style identifier generator with custom format
 *
 * Generates MongoDB-like identifiers with configurable prefix and components:
 * - 4-byte timestamp (seconds since Unix epoch)
 * - 5-byte machine/process identifier
 * - 3-byte random value
 *
 * Also provides ordered UUID generation and short ID generation options.
 * ***************************************
 * =======================================
 */

class TreesUuidv1
{
    private const DEFAULT_PREFIX = 'tre_';
    private const UUID_REGEX = '/^[a-z]{3}_[a-f0-9]{24}$/i';
    private const SHORT_ID_REGEX = '/^[a-z]{3}_[a-f0-9]{24}$/i';
    private const TIMESTAMP_BYTES = 4;
    private const MACHINE_ID_BYTES = 5;
    private const RANDOM_BYTES = 3;
    private const SHORT_ID_BYTES = 12; // 8-byte timestamp + 4-byte random

    /**
     * Generate a UUID with configured prefix
     *
     * @param string $prefix Optional custom prefix (default: 'tre_')
     * @return string Generated UUID
     * @throws \RuntimeException If random bytes cannot be generated
     */
    public static function generate(string $prefix = self::DEFAULT_PREFIX): string
    {
        if (strlen($prefix) !== 4 || !ctype_alpha(substr($prefix, 0, 3)) || substr($prefix, 3, 1) !== '_') {
            throw new \InvalidArgumentException('Prefix must be 3 alphabetic characters followed by underscore');
        }

        try {
            // 4-byte timestamp (seconds since Unix epoch)
            $timestamp = pack('N', time());

            // 5-byte machine identifier (using hostname and OS info)
            $machineId = self::getMachineIdentifier();

            // 3-byte random value
            $random = random_bytes(self::RANDOM_BYTES);

            return $prefix . bin2hex($timestamp . $machineId . $random);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate UUID: ' . $e->getMessage());
        }
    }

    /**
     * Validate UUID format and structure
     *
     * @param string $uuid UUID to validate
     * @param string $prefix Expected prefix (optional)
     * @return bool True if valid, false otherwise
     */
    public static function validate(string $uuid, string $prefix = self::DEFAULT_PREFIX): bool
    {
        // Basic format validation
        if (!preg_match(self::UUID_REGEX, $uuid)) {
            return false;
        }

        // Check prefix if provided
        if ($prefix !== '' && strpos($uuid, $prefix) !== 0) {
            return false;
        }

        // Extract timestamp for additional validation
        $hex = substr($uuid, strlen($prefix), 8);
        $timestamp = @unpack('N', hex2bin($hex))[1] ?? 0;

        // Validate timestamp range (1970-2106)
        return $timestamp > 0 && $timestamp < 0xFFFFFFFF;
    }

    /**
     * Parse UUID components
     *
     * @param string $uuid UUID to parse
     * @param string $prefix Expected prefix (optional)
     * @return array Parsed components [timestamp, machine_id, random]
     * @throws \InvalidArgumentException If UUID is invalid
     */
    public static function parse(string $uuid, string $prefix = self::DEFAULT_PREFIX): array
    {
        if (!self::validate($uuid, $prefix)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }

        $binary = hex2bin(substr($uuid, strlen($prefix)));

        return [
            'timestamp' => unpack('N', substr($binary, 0, self::TIMESTAMP_BYTES))[1],
            'machine_id' => bin2hex(substr($binary, self::TIMESTAMP_BYTES, self::MACHINE_ID_BYTES)),
            'random' => bin2hex(substr($binary, self::TIMESTAMP_BYTES + self::MACHINE_ID_BYTES, self::RANDOM_BYTES)),
            'prefix' => substr($uuid, 0, strlen($prefix))
        ];
    }

    /**
     * Generate ordered UUID (for database indexing benefits)
     *
     * @param string $prefix Optional custom prefix (default: 'tre_')
     * @return string Generated ordered UUID
     * @throws \RuntimeException If random bytes cannot be generated
     */
    public static function orderedGenerate(string $prefix = self::DEFAULT_PREFIX): string
    {
        if (strlen($prefix) !== 4 || !ctype_alpha(substr($prefix, 0, 3)) || substr($prefix, 3, 1) !== '_') {
            throw new \InvalidArgumentException('Prefix must be 3 alphabetic characters followed by underscore');
        }

        try {
            // 4-byte timestamp (seconds since epoch)
            $timestamp = pack('N', time());

            // 8-byte random data
            $random = random_bytes(8);

            return $prefix . bin2hex($timestamp . $random);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate ordered UUID: ' . $e->getMessage());
        }
    }

    /**
     * Get timestamp from UUID
     *
     * @param string $uuid UUID to extract timestamp from
     * @param string $prefix Expected prefix (optional)
     * @return int Unix timestamp
     * @throws \InvalidArgumentException If UUID is invalid
     */
    public static function getTimestamp(string $uuid, string $prefix = self::DEFAULT_PREFIX): int
    {
        if (!self::validate($uuid, $prefix)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }

        $hex = substr($uuid, strlen($prefix), 8);
        return unpack('N', hex2bin($hex))[1];
    }

    /**
     * Generate short ID (16 characters) for URLs
     *
     * @param string $prefix Optional custom prefix (default: 'tre_')
     * @return string Generated short ID
     * @throws \RuntimeException If random bytes cannot be generated
     */
    public static function shortGenerate(string $prefix = self::DEFAULT_PREFIX): string
    {
        if (strlen($prefix) !== 4 || !ctype_alpha(substr($prefix, 0, 3)) || substr($prefix, 3, 1) !== '_') {
            throw new \InvalidArgumentException('Prefix must be 3 alphabetic characters followed by underscore');
        }

        try {
            // 8-byte timestamp (milliseconds) + 4-byte random
            $binary = pack('J', (int)(microtime(true) * 1000)) . random_bytes(4);
            return $prefix . bin2hex($binary);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate short ID: ' . $e->getMessage());
        }
    }

    /**
     * Get machine identifier (cached for performance)
     *
     * @return string Binary machine identifier
     */
    private static function getMachineIdentifier(): string
    {
        static $machineId = null;

        if ($machineId === null) {
            $hostInfo = gethostname() . php_uname('n') . getmypid();
            $machineId = substr(hash('sha1', $hostInfo, true), 0, self::MACHINE_ID_BYTES);
        }

        return $machineId;
    }
}