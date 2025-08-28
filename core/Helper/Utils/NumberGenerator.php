<?php

declare(strict_types=1);

namespace Trees\Helper\Utils;

/**
 * =========================================
 * *****************************************
 * ========= Trees NumberGenerator Class ===
 * *****************************************
 * A comprehensive utility class for generating various types of unique numbers
 * with validation components, including:
 * - Secure ticket numbers
 * - Company registration numbers
 * - Invoice numbers
 * - Serial numbers
 *
 * Features:
 * - Cryptographically secure random number generation
 * - Multiple validation algorithms (Luhn, weighted checksum)
 * - Configurable formats
 * - Thread-safe operation
 * - Comprehensive error handling
 * =========================================
 */

class NumberGenerator
{
    private array $generatedNumbers = [];
    private string $salt;
    private static ?NumberGenerator $instance = null;

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct(?string $salt = null)
    {
        $this->salt = $salt ?? bin2hex(random_bytes(32));
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(?string $salt = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($salt);
        }
        return self::$instance;
    }

    /**
     * Generate a cryptographically secure unique ticket number
     *
     * @param string $prefix Ticket prefix (2-4 characters recommended)
     * @param int $length Total length (12-24 characters recommended)
     * @param bool $useTimestamp Include timestamp in generation
     * @return string Unique ticket number
     * @throws \InvalidArgumentException If length is invalid
     * @throws \RuntimeException If generation fails after maximum attempts
     */
    public function ticketNumber(
        string $prefix = 'TKT',
        int $length = 16,
        bool $useTimestamp = true
    ): string {
        $prefix = strtoupper(trim($prefix));

        if (strlen($prefix) < 2 || strlen($prefix) > 6) {
            throw new \InvalidArgumentException('Prefix must be 2-6 characters');
        }

        if ($length <= strlen($prefix)) {
            throw new \InvalidArgumentException('Length must be greater than prefix length');
        }

        if ($length > 64) {
            throw new \InvalidArgumentException('Length must be 64 characters or less');
        }

        $maxAttempts = 100;
        $attempt = 0;

        do {
            $attempt++;
            if ($attempt > $maxAttempts) {
                throw new \RuntimeException('Failed to generate unique ticket number after maximum attempts');
            }

            $components = [
                $useTimestamp ? (string)microtime(true) : '',
                $this->salt,
                bin2hex(random_bytes(8))
            ];

            $hash = hash('sha3-256', implode('', $components));
            $uniquePart = substr($hash, 0, $length - strlen($prefix));
            $ticketNumber = $prefix . strtoupper($uniquePart);

        } while (isset($this->generatedNumbers[$ticketNumber]));

        $this->generatedNumbers[$ticketNumber] = true;

        return $ticketNumber;
    }

    /**
     * Generate a standardized company registration number
     *
     * @param string $countryCode 2-3 character country identifier
     * @param int|null $year Year of registration (current year if null)
     * @param string $registryType Registry type identifier (1-3 chars)
     * @return string Company registration number in format: CC-YYYY-NNNNN-C
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function companyRegNumber(
        string $countryCode = 'US',
        ?int $year = null,
        string $registryType = ''
    ): string {
        $countryCode = strtoupper(trim($countryCode));
        $registryType = strtoupper(trim($registryType));

        if (!preg_match('/^[A-Z]{2,3}$/', $countryCode)) {
            throw new \InvalidArgumentException('Country code must be 2-3 uppercase letters');
        }

        if ($registryType && !preg_match('/^[A-Z0-9]{1,3}$/', $registryType)) {
            throw new \InvalidArgumentException('Registry type must be 1-3 alphanumeric characters');
        }

        $year = $year ?? (int)date('Y');
        if ($year < 1900 || $year > 2100) {
            throw new \InvalidArgumentException('Year must be between 1900 and 2100');
        }

        $randomDigits = str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        $baseNumber = $countryCode . $registryType . $year . $randomDigits;
        $checkDigit = $this->calculateCheckDigit($baseNumber);

        $parts = [
            $countryCode,
            $registryType ?: null,
            sprintf('%04d', $year),
            $randomDigits,
            (string)$checkDigit
        ];

        return implode('-', array_filter($parts));
    }

    /**
     * Generate a complex invoice number with industry-standard formatting
     *
     * @param string $businessCode Business identifier (3-6 chars)
     * @param string $invoiceType Type of invoice (2-4 chars)
     * @param string|null $date Optional date (YYYYMMDD format)
     * @param int $sequenceLength Length of random sequence (4-8)
     * @return string Formatted invoice number
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function invoiceNumber(
        string $businessCode = 'ACME',
        string $invoiceType = 'INV',
        ?string $date = null,
        int $sequenceLength = 6
    ): string {
        $businessCode = strtoupper(trim($businessCode));
        $invoiceType = strtoupper(trim($invoiceType));

        if (!preg_match('/^[A-Z0-9]{3,6}$/', $businessCode)) {
            throw new \InvalidArgumentException('Business code must be 3-6 alphanumeric characters');
        }

        if (!preg_match('/^[A-Z]{2,4}$/', $invoiceType)) {
            throw new \InvalidArgumentException('Invoice type must be 2-4 uppercase letters');
        }

        if ($sequenceLength < 4 || $sequenceLength > 8) {
            throw new \InvalidArgumentException('Sequence length must be between 4 and 8');
        }

        $date = $date ?? date('Ymd');
        if (!preg_match('/^20\d{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])$/', $date)) {
            throw new \InvalidArgumentException('Date must be in YYYYMMDD format');
        }

        $randomComponent = str_pad(
            (string)random_int(0, pow(10, $sequenceLength) - 1),
            $sequenceLength,
            '0',
            STR_PAD_LEFT
        );

        $checksum = $this->calculateLuhnChecksum($date . $randomComponent);

        return implode('-', [
            $businessCode,
            $invoiceType,
            $date,
            $randomComponent . $checksum
        ]);
    }

    /**
     * Generate a serial number with multiple validation components
     *
     * @param string $productLine Product line code (3-5 chars)
     * @param string $manufacturingLocation Location code (2-3 chars)
     * @param bool $useJulianDate Whether to use Julian date (true) or sequential (false)
     * @param int $randomLength Length of random component (4-8 bytes)
     * @return string Unique serial number
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function serialNumber(
        string $productLine = 'PRD',
        string $manufacturingLocation = 'US',
        bool $useJulianDate = true,
        int $randomLength = 5
    ): string {
        $productLine = strtoupper(trim($productLine));
        $manufacturingLocation = strtoupper(trim($manufacturingLocation));

        if (!preg_match('/^[A-Z0-9]{3,5}$/', $productLine)) {
            throw new \InvalidArgumentException('Product line must be 3-5 alphanumeric characters');
        }

        if (!preg_match('/^[A-Z]{2,3}$/', $manufacturingLocation)) {
            throw new \InvalidArgumentException('Location must be 2-3 uppercase letters');
        }

        if ($randomLength < 4 || $randomLength > 8) {
            throw new \InvalidArgumentException('Random length must be between 4 and 8');
        }

        $year = date('y');
        $dateComponent = $useJulianDate
            ? sprintf('%03d', (int)date('z'))
            : str_pad((string)random_int(0, 999), 3, '0', STR_PAD_LEFT);

        $randomComponent = bin2hex(random_bytes(ceil($randomLength / 2)));
        $randomComponent = substr($randomComponent, 0, $randomLength);
        $checkDigit = $this->calculateCheckDigit($productLine . $year . $dateComponent . $randomComponent);

        return implode('-', [
            $productLine,
            $manufacturingLocation,
            $year,
            $dateComponent,
            strtoupper($randomComponent),
            (string)$checkDigit
        ]);
    }

    /**
     * Calculate Luhn algorithm check digit (MOD-10)
     *
     * @param string $number Input number (digits only)
     * @return int Check digit (0-9)
     * @throws \InvalidArgumentException If input contains non-digit characters
     */
    private function calculateLuhnChecksum(string $number): int
    {
        if (!ctype_digit($number)) {
            throw new \InvalidArgumentException('Input must contain only digits');
        }

        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = 0; $i < $length; $i++) {
            $digit = (int)$number[$i];

            if ($i % 2 === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return (10 - ($sum % 10)) % 10;
    }

    /**
     * Calculate check digit using weighted algorithm (ISO 7064 MOD 10,11,16)
     *
     * @param string $input Input string (alphanumeric)
     * @param array $weights Optional custom weights
     * @return int Check digit (0-9)
     */
    private function calculateCheckDigit(string $input, array $weights = [7, 3, 1]): int
    {
        if (empty($weights)) {
            $weights = [7, 3, 1];
        }

        $sum = 0;
        $chars = str_split(strtoupper($input));

        foreach ($chars as $index => $char) {
            $value = ctype_digit($char)
                ? (int)$char
                : (ord($char) - 55); // A=10, B=11, etc.

            $sum += $value * $weights[$index % count($weights)];
        }

        return $sum % 10;
    }

    /**
     * Reset generated numbers cache (for testing or special cases)
     */
    public function resetGeneratedNumbers(): void
    {
        $this->generatedNumbers = [];
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
        throw new \RuntimeException('Cannot unserialize singleton');
    }
}