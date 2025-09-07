<?php

declare(strict_types=1);

namespace Trees\Helper\Utils;

/**
 * =========================================
 * *****************************************
 * ======== Universal CodeGenerator Class ========
 * *****************************************
 * A comprehensive code generation utility class that works with any project
 * =========================================
 */

class CodeGenerator
{
    private const DEFAULT_FORMAT = 'standard';
    private const MAX_UNIQUENESS_ATTEMPTS = 10;
    
    // Available code formats
    public const FORMAT_STANDARD = 'standard';
    public const FORMAT_SIMPLE = 'simple';
    public const FORMAT_QR = 'qr';
    public const FORMAT_UUID = 'uuid';
    public const FORMAT_NUMERIC = 'numeric';
    
    private $uniquenessChecker;
    private $usedCodes = [];
    private $prefix = '';
    private $separator = '-';
    
    /**
     * Constructor
     *
     * @param callable|null $uniquenessChecker Function to check if code exists (should return bool)
     * @param string $prefix Prefix for generated codes
     * @param string $separator Separator character
     */
    public function __construct(
        ?callable $uniquenessChecker = null,
        string $prefix = '',
        string $separator = '-'
    ) {
        $this->uniquenessChecker = $uniquenessChecker;
        $this->prefix = $prefix;
        $this->separator = $separator;
    }
    
    /**
     * Generate a unique code based on the specified format
     *
     * @param int|null $id1 First ID (e.g., event ID, project ID)
     * @param int|null $id2 Second ID (e.g., attendee ID, item ID)
     * @param string $format The format type
     * @param array $options Additional options for code generation
     * @return string Unique code
     * @throws \InvalidArgumentException
     */
    public function generate(
        ?int $id1 = null, 
        ?int $id2 = null, 
        string $format = self::DEFAULT_FORMAT,
        array $options = []
    ): string {
        $this->validateParameters($id1, $id2, $format);
        
        $code = match($format) {
            self::FORMAT_STANDARD => $this->generateStandardCode($id1, $id2, $options),
            self::FORMAT_SIMPLE => $this->generateSimpleCode($options),
            self::FORMAT_QR => $this->generateQRCode($id1, $id2, $options),
            self::FORMAT_UUID => $this->generateUuidCode($options),
            self::FORMAT_NUMERIC => $this->generateNumericCode($options),
            default => throw new \InvalidArgumentException("Invalid code format: {$format}")
        };
        
        return $this->ensureUniqueness($code, $format, $id1, $id2, $options);
    }
    
    /**
     * Generate a standard code
     */
    private function generateStandardCode(int $id1, int $id2, array $options): string
    {
        $id1Padded = str_pad((string)$id1, $options['id1_length'] ?? 3, '0', STR_PAD_LEFT);
        $id2Padded = str_pad((string)$id2, $options['id2_length'] ?? 4, '0', STR_PAD_LEFT);
        $randomLength = $options['random_length'] ?? 6;
        $randomString = $this->generateRandomString($randomLength);
        
        $prefix = $options['prefix'] ?? $this->prefix ?: 'COD';
        $separator = $options['separator'] ?? $this->separator;
        
        return "{$prefix}{$separator}{$id1Padded}{$separator}{$id2Padded}{$separator}{$randomString}";
    }
    
    /**
     * Generate a simple code
     */
    private function generateSimpleCode(array $options): string
    {
        $timestampFormat = $options['timestamp_format'] ?? 'ymd';
        $timestamp = date($timestampFormat);
        $randomLength = $options['random_length'] ?? 6;
        $random = $this->generateRandomString($randomLength);
        
        $prefix = $options['prefix'] ?? $this->prefix ?: 'TKT';
        $separator = $options['separator'] ?? $this->separator;
        
        return "{$prefix}{$separator}{$timestamp}{$separator}{$random}";
    }
    
    /**
     * Generate a QR code compatible code
     */
    private function generateQRCode(int $id1, int $id2, array $options): string
    {
        $id1Hex = strtoupper(dechex($id1));
        $id2Hex = strtoupper(dechex($id2));
        $randomBytes = $options['random_bytes'] ?? 3;
        $randomHex = strtoupper(substr(bin2hex(random_bytes($randomBytes)), 0, 5));
        
        return $id1Hex . $id2Hex . $randomHex;
    }
    
    /**
     * Generate a UUID-based code
     */
    private function generateUuidCode(array $options): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant
        
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        
        if (!empty($options['short_uuid'] ?? false)) {
            return str_replace('-', '', $uuid);
        }
        
        return $uuid;
    }
    
    /**
     * Generate a numeric code
     */
    private function generateNumericCode(array $options): string
    {
        $length = $options['length'] ?? 12;
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        
        return (string)random_int($min, $max);
    }
    
    /**
     * Generate a random string
     */
    private function generateRandomString(int $length = 6, string $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string
    {
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
    
    /**
     * Ensure the generated code is unique
     */
    private function ensureUniqueness(string $initialCode, string $format, ?int $id1, ?int $id2, array $options): string
    {
        $code = $initialCode;
        $attempts = 0;
        
        while ($attempts < self::MAX_UNIQUENESS_ATTEMPTS) {
            // Check in-memory cache first (for loop/foreach scenarios)
            if (!in_array($code, $this->usedCodes)) {
                // If no external checker provided or checker returns false (not exists)
                if ($this->uniquenessChecker === null || !call_user_func($this->uniquenessChecker, $code)) {
                    $this->usedCodes[] = $code;
                    return $code;
                }
            }
            
            // Regenerate code
            $code = $this->regenerateCode($format, $id1, $id2, $options);
            $attempts++;
        }
        
        // If max attempts reached, append timestamp/microtime
        return $code . $this->separator . microtime(true);
    }
    
    /**
     * Regenerate code based on format
     */
    private function regenerateCode(string $format, ?int $id1, ?int $id2, array $options): string
    {
        return match($format) {
            self::FORMAT_STANDARD => $this->generateStandardCode($id1, $id2, $options),
            self::FORMAT_SIMPLE => $this->generateSimpleCode($options),
            self::FORMAT_QR => $this->generateQRCode($id1, $id2, $options),
            self::FORMAT_UUID => $this->generateUuidCode($options),
            self::FORMAT_NUMERIC => $this->generateNumericCode($options),
            default => $this->generateSimpleCode($options)
        };
    }
    
    /**
     * Validate input parameters
     */
    private function validateParameters(?int $id1, ?int $id2, string $format): void
    {
        if (in_array($format, [self::FORMAT_STANDARD, self::FORMAT_QR])) {
            if ($id1 === null || $id2 === null) {
                throw new \InvalidArgumentException(
                    "Both IDs are required for {$format} format"
                );
            }
            
            if ($id1 <= 0 || $id2 <= 0) {
                throw new \InvalidArgumentException(
                    "IDs must be positive integers"
                );
            }
        }
        
        $availableFormats = [
            self::FORMAT_STANDARD,
            self::FORMAT_SIMPLE,
            self::FORMAT_QR,
            self::FORMAT_UUID,
            self::FORMAT_NUMERIC
        ];
        
        if (!in_array($format, $availableFormats)) {
            throw new \InvalidArgumentException(
                "Invalid format. Available formats: " . implode(', ', $availableFormats)
            );
        }
    }
    
    /**
     * Clear the in-memory cache of used codes
     */
    public function clearCache(): void
    {
        $this->usedCodes = [];
    }
    
    /**
     * Get available code formats
     */
    public static function getAvailableFormats(): array
    {
        return [
            self::FORMAT_STANDARD => 'Standard format (COD-001-0001-ABC123)',
            self::FORMAT_SIMPLE => 'Simple format (TKT-240907-ABC123)',
            self::FORMAT_QR => 'QR format (7B19A5F)',
            self::FORMAT_UUID => 'UUID format (6ba7b810-9dad-11d1-80b4-00c04fd430c8)',
            self::FORMAT_NUMERIC => 'Numeric format (123456789012)',
        ];
    }
    
    /**
     * Set a custom uniqueness checker function
     */
    public function setUniquenessChecker(callable $checker): void
    {
        $this->uniquenessChecker = $checker;
    }
    
    /**
     * Set prefix for generated codes
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }
    
    /**
     * Set separator character
     */
    public function setSeparator(string $separator): void
    {
        $this->separator = $separator;
    }
}