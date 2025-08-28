<?php

declare(strict_types=1);

use Trees\Http\Request;

/**
 * =======================================
 * ***************************************
 * ======== Trees Other Function ========
 * ***************************************
 * =======================================
 */

if (!function_exists('str_slug')) {
    /**
     * Generate a URL-friendly slug from a given string
     *
     * @param string $string The string to convert
     * @param string $separator The word separator (default: '-')
     * @return string The generated slug
     */
    function str_slug(string $string, string $separator = '-'): string
    {
        // Convert apostrophes and other special characters to spaces
        $string = str_replace(["'", "`", "’", "‘"], "", $string); // Remove apostrophes completely

        // Convert all characters to ASCII
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

        // Remove all non-alphanumeric characters except spaces and the separator
        $string = preg_replace("/[^a-zA-Z0-9\s]/", "", $string);

        // Convert spaces to the separator
        $string = preg_replace("/\s+/", $separator, $string);

        // Convert to lowercase
        $string = strtolower($string);

        // Trim any leading/trailing separators
        $string = trim($string, $separator);

        // Remove duplicate separators
        $string = preg_replace("/{$separator}+/", $separator, $string);

        return $string;
    }
}

if (!function_exists('secureUrl')) {
    /**
     * Generate URL with additional security checks for user input
     *
     * @param string $path The base path/URL
     * @param array $params Optional query parameters
     * @return string|null The generated URL or null on validation failure
     */
    function secureUrl(string $path, array $params = []): ?string
    {
        // Additional security options
        $securityOptions = [
            'validate_url' => true,
            'sanitize_path' => true,
            'max_url_length' => 1024, // Stricter limit
            'allowed_schemes' => ['https'] // HTTPS only for security
        ];

        // Sanitize all parameter values
        $sanitizedParams = [];
        foreach ($params as $key => $value) {
            if (is_string($value)) {
                $sanitizedParams[filter_var($key, FILTER_SANITIZE_STRING)] =
                    filter_var($value, FILTER_SANITIZE_STRING);
            } elseif (is_numeric($value)) {
                $sanitizedParams[filter_var($key, FILTER_SANITIZE_STRING)] = $value;
            }
        }

        return url($path, $sanitizedParams, $securityOptions);
    }
}

if (!function_exists('getExcerpt')) {
    /**
     * Extracts an excerpt from a description while ensuring it ends on a complete word.
     *
     * @param string $text The full description text
     * @param int $maxLength Maximum length of the excerpt (in characters)
     * @param string $suffix Suffix to append if text is truncated (default: '...')
     * @return string The extracted excerpt
     * @throws InvalidArgumentException if input parameters are invalid
     */
    function getExcerpt(string $text, int $maxLength = 150, string $suffix = '...'): string
    {
        // Validate input parameters
        if ($maxLength <= 0) {
            throw new \InvalidArgumentException('Max length must be greater than 0');
        }

        if (empty($text)) {
            return '';
        }

        // Remove HTML tags and trim whitespace
        $cleanText = trim(strip_tags($text));

        // If text is shorter than max length, return it as-is
        if (mb_strlen($cleanText) <= $maxLength) {
            return $cleanText;
        }

        // Truncate to max length (including suffix length)
        $truncated = mb_substr($cleanText, 0, $maxLength - mb_strlen($suffix));

        // Find the last space in the truncated text
        $lastSpace = mb_strrpos($truncated, ' ');

        // If no space found (single long word), just truncate
        if ($lastSpace === false) {
            return $truncated . $suffix;
        }

        // Trim to the last space to ensure we end on a word boundary
        $excerpt = mb_substr($truncated, 0, $lastSpace);

        // Remove any trailing punctuation
        $excerpt = rtrim($excerpt, ',;.!?');

        return $excerpt . $suffix;
    }
}

if (!function_exists('pluralize')) {
    function pluralize(string $word): string
    {
        $lastChar = strtolower($word[strlen($word) - 1]);
        $lastTwoChars = substr(strtolower($word), -2);

        $exceptions = [
            'child' => 'children',
            'person' => 'people',
            'man' => 'men',
            'woman' => 'women',
            'tooth' => 'teeth',
            'foot' => 'feet',
            'mouse' => 'mice',
            'goose' => 'geese',
        ];

        if (array_key_exists(strtolower($word), $exceptions)) {
            return $exceptions[strtolower($word)];
        }

        if ($lastChar === 'y' && !in_array($lastTwoChars, ['ay', 'ey', 'iy', 'oy', 'uy'])) {
            return substr($word, 0, -1) . 'ies';
        }

        if (in_array($lastChar, ['s', 'x', 'z']) || in_array($lastTwoChars, ['ch', 'sh'])) {
            return $word . 'es';
        }

        return $word . 's';
    }
}

if (!function_exists('singularize')) {
    function singularize(string $word): string
    {
        $exceptions = [
            'children' => 'child',
            'people' => 'person',
            'men' => 'man',
            'women' => 'woman',
            'teeth' => 'tooth',
            'feet' => 'foot',
            'mice' => 'mouse',
            'geese' => 'goose',
        ];

        if (array_key_exists(strtolower($word), $exceptions)) {
            return $exceptions[strtolower($word)];
        }

        if (str_ends_with($word, 'ies')) {
            return substr($word, 0, -3) . 'y';
        }

        if (str_ends_with($word, 'es')) {
            $base = substr($word, 0, -2);
            $lastChar = substr($base, -1);

            if (
                in_array($lastChar, ['s', 'x', 'z']) ||
                str_ends_with($base, 'ch') ||
                str_ends_with($base, 'sh')
            ) {
                return $base;
            }
        }

        if (str_ends_with($word, 's')) {
            return substr($word, 0, -1);
        }

        return $word;
    }
}

if (!function_exists('extract_links')) {
    function extract_links(string $links): array
    {
        // Split the comma-separated links
        $linkArray = array_map('trim', explode(',', $links));

        $result = [
            'tiktok' => null,
            'x' => null,
            'instagram' => null,
            'facebook' => null,
            'others' => [],
        ];

        foreach ($linkArray as $url) {
            if (stripos($url, 'tiktok.com') !== false) {
                $result['tiktok'] = $url;
            } elseif (stripos($url, 'x.com') !== false || stripos($url, 'twitter.com') !== false) {
                $result['x'] = $url;
            } elseif (stripos($url, 'instagram.com') !== false) {
                $result['instagram'] = $url;
            } elseif (stripos($url, 'facebook.com') !== false) {
                $result['facebook'] = $url;
            } else {
                $result['others'][] = $url;
            }
        }

        return $result;
    }
}

if (!function_exists('numberRange')) {
    /**
     * Generates an array of numbers from 1 to a specified maximum
     *
     * @param int $max The maximum number in the range
     * @return array An array of numbers from 1 to $max
     */
    function numberRange($max)
    {
        return range(1, $max);
    }
}

if (!function_exists('toCamelCase')) {
    function toCamelCase($string)
    {
        // Trim whitespace and convert all words to lowercase first
        $string = strtolower(trim($string));

        // Replace any underscores, hyphens, or spaces with word boundaries
        $string = preg_replace('/[_\-\s]+/', ' ', $string);

        // Capitalize the first letter of each word except the first one
        $string = ucwords($string);

        // Remove all spaces and lowercase the first character
        $string = str_replace(' ', '', $string);
        $string = lcfirst($string);

        return $string;
    }
}

/**
 * Generates a standardized order number with validation components
 *
 * @param string $customerCode Customer identifier (3-6 alphanumeric chars)
 * @param string $productType Product category code (2-4 alphabetic chars)
 * @param string|null $date Date in YYYYMMDD format (default: current date)
 * @param int $sequenceLength Length of random sequence (4-6)
 * @param string $separator Character to separate components (-, /, ., or empty)
 * @return string Formatted order number
 * @throws InvalidArgumentException If parameters are invalid
 */
function generateOrderNumber(
    string $customerCode = 'CUST',
    string $productType = 'PROD',
    ?string $date = null,
    int $sequenceLength = 5,
    string $separator = '-'
): string {
    // Validate and normalize inputs
    $customerCode = strtoupper(trim($customerCode));
    $productType = strtoupper(trim($productType));

    if (!preg_match('/^[A-Z0-9]{3,6}$/', $customerCode)) {
        throw new InvalidArgumentException('Customer code must be 3-6 alphanumeric characters');
    }

    if (!preg_match('/^[A-Z]{2,4}$/', $productType)) {
        throw new InvalidArgumentException('Product type must be 2-4 uppercase letters');
    }

    if ($sequenceLength < 4 || $sequenceLength > 6) {
        throw new InvalidArgumentException('Sequence length must be between 4 and 6');
    }

    if (!in_array($separator, ['-', '', '/', '.'])) {
        throw new InvalidArgumentException('Separator must be one of: -, empty, /, .');
    }

    $date = $date ?? date('Ymd');
    if (!preg_match('/^20\d{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])$/', $date)) {
        throw new InvalidArgumentException('Date must be in YYYYMMDD format');
    }

    // Generate components
    $year = substr($date, 2, 2); // Last 2 digits of year
    $month = substr($date, 4, 2);
    $randomComponent = str_pad(
        (string)random_int(0, pow(10, $sequenceLength) - 1),
        $sequenceLength,
        '0',
        STR_PAD_LEFT
    );

    // Calculate checksums
    $luhnCheck = calculateLuhnChecksum($randomComponent);
    $weightedCheck = calculateWeightedChecksum($customerCode . $productType . $year . $month . $randomComponent);

    // Build and return the order number
    $components = [
        $customerCode,
        $productType,
        $year . $month,
        $randomComponent . $luhnCheck,
        (string)$weightedCheck
    ];

    return implode($separator, array_filter($components));
}

/**
 * Calculate Luhn checksum digit
 */
function calculateLuhnChecksum(string $number): int
{
    if (!ctype_digit($number)) {
        throw new InvalidArgumentException('Luhn input must contain only digits');
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
 * Calculate weighted checksum digit (ISO 7064 MOD 10,11,16)
 */
function calculateWeightedChecksum(string $input, array $weights = [7, 3, 1]): int
{
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

if (!function_exists('calculate_percentage')) {
    /**
     * Calculates the percentage difference between old and new prices
     *
     * @param float $oldPrice The original price
     * @param float $newPrice The new price
     * @param bool $format Whether to format the output with % sign (default: false)
     * @return int|string Returns the percentage difference as whole number (negative for decrease, positive for increase)
     * @throws InvalidArgumentException If prices are invalid
     */
    function calculate_percentage(
        float $oldPrice,
        float $newPrice,
        bool $format = false
    ): int|string {
        // Validate inputs
        if ($oldPrice <= 0) {
            throw new InvalidArgumentException('Old price must be greater than 0');
        }

        // Calculate percentage difference and round to nearest integer
        $percentage = (($newPrice - $oldPrice) / $oldPrice) * 100;
        $rounded = (int)round($percentage);

        // Return formatted or raw value
        return $format ? sprintf('%+d%%', $rounded) : $rounded;
    }
}

if (!function_exists('fetchData')) {
    /**
     * Fetches data from a model by finding a record matching the given key.
     * 
     * @param string $model The model class name (e.g., 'Setting')
     * @param string $key The key to search for (default 'name' field)
     * @param mixed $default Default value if not found
     * @param bool $returnModel Return entire model instance instead of just value
     * @return mixed The requested value, model instance, or default
     */
    function fetchData(string $model, string $key, $default = null, $keyField = 'name', bool $returnModel = false)
    {
        // Validate the model exists
        if (!class_exists($model)) {
            return $default;
        }

        try {
            // Find the first matching record
            $result = $model::first([$keyField => $key]);

            if (!$result) {
                return $default;
            }

            return $returnModel ? $result : $result->value;
        } catch (\Exception $e) {
            // Log error if needed
            return $default;
        }
    }
}
