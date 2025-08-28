<?php

declare(strict_types=1);

namespace Trees\Helper\Utils;

/**
 * =========================================
 * *****************************************
 * ======== Trees StringUtils Class ========
 * *****************************************
 * A comprehensive string manipulation utility class with:
 * - Multi-byte string support
 * - Case conversion methods
 * - String formatting utilities
 * - Validation and sanitization
 * - Encoding/decoding capabilities
 *
 * Features:
 * - Fluent interface
 * - Comprehensive character encoding support
 * - Secure string handling
 * - Performance optimizations
 * =========================================
 */

class StringUtils
{
    private string $string;

    public function __construct(string $string = '')
    {
        $this->string = $string;
    }

    /**
     * Create a new StringUtils instance (fluent interface)
     */
    public static function create(string $string = ''): self
    {
        return new static($string);
    }

    /**
     * Get the raw string value
     */
    public function get(): string
    {
        return $this->string;
    }

    /**
     * Create excerpt of the string
     */
    public function excerpt(int $length = 100, string $ending = '...', bool $preserveWords = true): string
    {
        if (mb_strlen($this->string) <= $length) {
            return $this->string;
        }

        if ($preserveWords) {
            $excerpt = mb_substr($this->string, 0, $length);
            $lastSpace = mb_strrpos($excerpt, ' ');
            if ($lastSpace !== false) {
                $excerpt = mb_substr($excerpt, 0, $lastSpace);
            }
            return $excerpt . $ending;
        }

        return mb_substr($this->string, 0, $length) . $ending;
    }

    /**
     * Convert to uppercase
     */
    public function toUpper(): self
    {
        $this->string = mb_strtoupper($this->string);
        return $this;
    }

    /**
     * Convert to lowercase
     */
    public function toLower(): self
    {
        $this->string = mb_strtolower($this->string);
        return $this;
    }

    /**
     * Convert to title case
     */
    public function toTitle(): self
    {
        $this->string = mb_convert_case($this->string, MB_CASE_TITLE);
        return $this;
    }

    /**
     * Convert to camelCase
     */
    public function toCamelCase(): self
    {
        $this->string = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $this->string))));
        return $this;
    }

    /**
     * Convert to snake_case
     */
    public function toSnakeCase(): self
    {
        $this->string = preg_replace('/[^A-Za-z0-9]+/', '_', $this->string);
        $this->string = mb_strtolower(trim($this->string, '_'));
        return $this;
    }

    /**
     * Convert to kebab-case
     */
    public function toKebabCase(): self
    {
        $this->string = preg_replace('/[^A-Za-z0-9]+/', '-', $this->string);
        $this->string = mb_strtolower(trim($this->string, '-'));
        return $this;
    }

    /**
     * Convert to URL-friendly slug
     */
    public function toSlug(string $separator = '-', string $language = 'en'): self
    {
        // Transliterate non-ASCII characters
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $this->string);
        $slug = preg_replace('/[^a-zA-Z0-9\/_|+ -]/', '', $slug);
        $slug = mb_strtolower(trim($slug, ' '));
        $slug = preg_replace('/[\/_|+ -]+/', $separator, $slug);
        $this->string = trim($slug, $separator);
        return $this;
    }

    /**
     * Truncate string with optional word boundary preservation
     */
    public function truncate(int $length, string $ending = '...', bool $preserveWords = true): self
    {
        if (mb_strlen($this->string) <= $length) {
            return $this;
        }

        if ($preserveWords) {
            $truncated = mb_substr($this->string, 0, $length);
            $lastSpace = mb_strrpos($truncated, ' ');
            if ($lastSpace !== false) {
                $truncated = mb_substr($truncated, 0, $lastSpace);
            }
            $this->string = $truncated . $ending;
        } else {
            $this->string = mb_substr($this->string, 0, $length) . $ending;
        }

        return $this;
    }

    /**
     * Check if string contains substring
     */
    public function contains(string $substring, bool $caseSensitive = true): bool
    {
        return $caseSensitive
            ? mb_strpos($this->string, $substring) !== false
            : mb_stripos($this->string, $substring) !== false;
    }

    /**
     * Check if string starts with prefix
     */
    public function startsWith(string $prefix, bool $caseSensitive = true): bool
    {
        if ($caseSensitive) {
            return mb_substr($this->string, 0, mb_strlen($prefix)) === $prefix;
        }
        return mb_strtolower(mb_substr($this->string, 0, mb_strlen($prefix))) === mb_strtolower($prefix);
    }

    /**
     * Check if string ends with suffix
     */
    public function endsWith(string $suffix, bool $caseSensitive = true): bool
    {
        if ($caseSensitive) {
            return mb_substr($this->string, -mb_strlen($suffix)) === $suffix;
        }
        return mb_strtolower(mb_substr($this->string, -mb_strlen($suffix))) === mb_strtolower($suffix);
    }

    /**
     * Replace all occurrences of search string
     */
    public function replace(string $search, string $replace): self
    {
        $this->string = str_replace($search, $replace, $this->string);
        return $this;
    }

    /**
     * Replace first occurrence of search string
     */
    public function replaceFirst(string $search, string $replace): self
    {
        $position = mb_strpos($this->string, $search);
        if ($position !== false) {
            $this->string = substr_replace($this->string, $replace, $position, mb_strlen($search));
        }
        return $this;
    }

    /**
     * Replace last occurrence of search string
     */
    public function replaceLast(string $search, string $replace): self
    {
        $position = mb_strrpos($this->string, $search);
        if ($position !== false) {
            $this->string = substr_replace($this->string, $replace, $position, mb_strlen($search));
        }
        return $this;
    }

    /**
     * Split string by delimiter
     */
    public function split(string $delimiter, int $limit = PHP_INT_MAX): array
    {
        return explode($delimiter, $this->string, $limit);
    }

    /**
     * Get string length
     */
    public function length(): int
    {
        return mb_strlen($this->string);
    }

    /**
     * Trim whitespace or specified characters from both ends
     */
    public function trim(string $characterMask = " \t\n\r\0\x0B"): self
    {
        $this->string = trim($this->string, $characterMask);
        return $this;
    }

    /**
     * Trim whitespace or specified characters from start
     */
    public function trimStart(string $characterMask = " \t\n\r\0\x0B"): self
    {
        $this->string = ltrim($this->string, $characterMask);
        return $this;
    }

    /**
     * Trim whitespace or specified characters from end
     */
    public function trimEnd(string $characterMask = " \t\n\r\0\x0B"): self
    {
        $this->string = rtrim($this->string, $characterMask);
        return $this;
    }

    /**
     * Strip HTML and PHP tags
     */
    public function stripTags(string $allowableTags = ''): self
    {
        $this->string = strip_tags($this->string, $allowableTags);
        return $this;
    }

    /**
     * Pad string to specified length
     */
    public function pad(int $length, string $padString = ' ', int $padType = STR_PAD_RIGHT): self
    {
        $this->string = str_pad($this->string, $length, $padString, $padType);
        return $this;
    }

    /**
     * Convert to ASCII (with transliteration)
     */
    public function toAscii(): self
    {
        $this->string = preg_replace('/[^\x20-\x7E]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $this->string));
        return $this;
    }

    /**
     * Repeat string
     */
    public function repeat(int $multiplier): self
    {
        $this->string = str_repeat($this->string, $multiplier);
        return $this;
    }

    /**
     * Reverse string
     */
    public function reverse(): self
    {
        $this->string = strrev($this->string);
        return $this;
    }

    /**
     * Uppercase first character
     */
    public function ucfirst(): self
    {
        $this->string = ucfirst($this->string);
        return $this;
    }

    /**
     * Lowercase first character
     */
    public function lcfirst(): self
    {
        $this->string = lcfirst($this->string);
        return $this;
    }

    /**
     * Capitalize all words
     */
    public function capitalizeWords(): self
    {
        $this->string = ucwords($this->string);
        return $this;
    }

    /**
     * Limit string length with word boundary preservation
     */
    public function limit(int $limit = 100, string $end = '...', bool $preserveWords = true): self
    {
        return $this->truncate($limit, $end, $preserveWords);
    }

    /**
     * Sanitize string (strip tags and escape HTML)
     */
    public function sanitize(bool $stripTags = true): self
    {
        $string = $stripTags ? strip_tags($this->string) : $this->string;
        $this->string = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return $this;
    }

    /**
     * Count words in string
     */
    public function wordCount(): int
    {
        return str_word_count($this->string);
    }

    /**
     * Mask parts of the string (for sensitive data)
     */
    public function mask(int $startLength = 3, int $endLength = 3, string $maskChar = '*'): self
    {
        $strLength = mb_strlen($this->string);
        $maskLength = $strLength - $startLength - $endLength;

        if ($maskLength > 0) {
            $start = mb_substr($this->string, 0, $startLength);
            $end = mb_substr($this->string, -$endLength);
            $this->string = $start . str_repeat($maskChar, $maskLength) . $end;
        }

        return $this;
    }

    /**
     * Convert to base64 encoding
     */
    public function toBase64(): self
    {
        $this->string = base64_encode($this->string);
        return $this;
    }

    /**
     * Convert from base64 encoding
     */
    public function fromBase64(): self
    {
        $decoded = base64_decode($this->string, true);
        $this->string = $decoded !== false ? $decoded : $this->string;
        return $this;
    }

    /**
     * Generate hash of string
     */
    public function hash(string $algorithm = 'sha256'): string
    {
        return hash($algorithm, $this->string);
    }

    /**
     * Check if string matches a regex pattern
     */
    public function matches(string $pattern): bool
    {
        return (bool)preg_match($pattern, $this->string);
    }

    /**
     * Remove whitespace from string
     */
    public function removeWhitespace(): self
    {
        $this->string = preg_replace('/\s+/', '', $this->string);
        return $this;
    }

    /**
     * Count occurrences of substring
     */
    public function countSubstring(string $substring, bool $caseSensitive = true): int
    {
        return $caseSensitive
            ? mb_substr_count($this->string, $substring)
            : mb_substr_count(mb_strtolower($this->string), mb_strtolower($substring));
    }

    /**
     * Check if string is empty
     */
    public function isEmpty(): bool
    {
        return $this->string === '';
    }

    /**
     * Check if string is not empty
     */
    public function isNotEmpty(): bool
    {
        return $this->string !== '';
    }

    /**
     * Convert to JSON string
     */
    public function toJson(): string
    {
        return json_encode($this->string, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Convert string to boolean
     */
    public function toBoolean(): bool
    {
        return filter_var($this->string, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get string representation
     */
    public function __toString(): string
    {
        return $this->string;
    }
}