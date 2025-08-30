<?php

declare(strict_types=1);

namespace Trees\Helper\Cities;

/**
 * =======================================
 * ***************************************
 * ========== Trees Cities Class =========
 * ***************************************
 * Provides city data and filtering capabilities.
 *
 * Features:
 * - City data with names, states, and country codes
 * - Filtering capabilities by country, state, and inclusion/exclusion lists
 * - Data validation and type safety
 * - Immutable operations
 * - Caching for better performance
 * =======================================
 */

class Cities
{
    /**
     * @var array Complete list of cities with their details
     */
    private static $cities = [
        'NG' => [
            'ABV' => ['name' => 'Abuja', 'state' => 'FCT', 'country_code' => 'NG'],
            'LAG' => ['name' => 'Lagos', 'state' => 'Lagos', 'country_code' => 'NG'],
            'KAN' => ['name' => 'Kano', 'state' => 'Kano', 'country_code' => 'NG'],
            'IBD' => ['name' => 'Ibadan', 'state' => 'Oyo', 'country_code' => 'NG'],
            'PHT' => ['name' => 'Port Harcourt', 'state' => 'Rivers', 'country_code' => 'NG'],
            'BEN' => ['name' => 'Benin City', 'state' => 'Edo', 'country_code' => 'NG'],
            'KAD' => ['name' => 'Kaduna', 'state' => 'Kaduna', 'country_code' => 'NG'],
            'ABK' => ['name' => 'Abakaliki', 'state' => 'Ebonyi', 'country_code' => 'NG'],
            'ABJ' => ['name' => 'Abeokuta', 'state' => 'Ogun', 'country_code' => 'NG'],
            'AKR' => ['name' => 'Akure', 'state' => 'Ondo', 'country_code' => 'NG'],
            'OSG' => ['name' => 'Osogbo', 'state' => 'Osun', 'country_code' => 'NG'],
            'ENU' => ['name' => 'Enugu', 'state' => 'Enugu', 'country_code' => 'NG'],
            'SOK' => ['name' => 'Sokoto', 'state' => 'Sokoto', 'country_code' => 'NG'],
            'JOS' => ['name' => 'Jos', 'state' => 'Plateau', 'country_code' => 'NG'],
            'ILR' => ['name' => 'Ilorin', 'state' => 'Kwara', 'country_code' => 'NG'],
            'OWR' => ['name' => 'Owerri', 'state' => 'Imo', 'country_code' => 'NG'],
            'YOL' => ['name' => 'Yola', 'state' => 'Adamawa', 'country_code' => 'NG'],
            'MAK' => ['name' => 'Makurdi', 'state' => 'Benue', 'country_code' => 'NG'],
            'GMB' => ['name' => 'Gombe', 'state' => 'Gombe', 'country_code' => 'NG'],
            'BAU' => ['name' => 'Bauchi', 'state' => 'Bauchi', 'country_code' => 'NG'],
            'MIN' => ['name' => 'Maiduguri', 'state' => 'Borno', 'country_code' => 'NG'],
            'BIC' => ['name' => 'Birnin Kebbi', 'state' => 'Kebbi', 'country_code' => 'NG'],
            'LOK' => ['name' => 'Lokoja', 'state' => 'Kogi', 'country_code' => 'NG'],
            'JAL' => ['name' => 'Jalingo', 'state' => 'Taraba', 'country_code' => 'NG'],
            'DUT' => ['name' => 'Dutse', 'state' => 'Jigawa', 'country_code' => 'NG'],
            'DAM' => ['name' => 'Damaturu', 'state' => 'Yobe', 'country_code' => 'NG'],
            'NSU' => ['name' => 'Nsukka', 'state' => 'Enugu', 'country_code' => 'NG'],
            'OFU' => ['name' => 'Ofa', 'state' => 'Kwara', 'country_code' => 'NG'],
            'IKD' => ['name' => 'Ikot Ekpene', 'state' => 'Akwa Ibom', 'country_code' => 'NG'],
            'IKJ' => ['name' => 'Ikeja', 'state' => 'Lagos', 'country_code' => 'NG'],
            'VIC' => ['name' => 'Victoria Island', 'state' => 'Lagos', 'country_code' => 'NG'],
        ],
        // Add other countries here in the future
    ];

    /**
     * @var array Cache for filtered results
     */
    private static $cache = [];

    /**
     * Get cities with optional filtering and output format.
     *
     * @param string $countryCode Country code to get cities for (e.g., 'NG')
     * @param array $include Only include these cities by code or state.
     * @param array $exclude Exclude these cities by code or state.
     * @param bool $simplified Return only code and name if true.
     * @return array Filtered and formatted cities.
     */
    public static function getCities(string $countryCode, array $include = [], array $exclude = [], bool $simplified = false): array
    {
        $countryCode = strtoupper($countryCode);
        $cacheKey = md5(serialize([$countryCode, $include, $exclude, $simplified]));

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        if (!isset(self::$cities[$countryCode])) {
            return [];
        }

        $result = self::$cities[$countryCode];

        // Apply includes if specified
        if (!empty($include)) {
            $included = [];
            foreach ($include as $item) {
                if (isset($result[$item])) {
                    $included[$item] = $result[$item];
                } else {
                    // Assume it's a state if not a city code
                    $stateCities = array_filter($result, fn($city) => $city['state'] === $item);
                    $included = array_merge($included, $stateCities);
                }
            }
            $result = $included;
        }

        // Apply excludes if specified
        if (!empty($exclude)) {
            foreach ($exclude as $item) {
                if (isset($result[$item])) {
                    unset($result[$item]);
                } else {
                    // Assume it's a state if not a city code
                    $result = array_filter($result, fn($city) => $city['state'] !== $item);
                }
            }
        }

        // Simplify output if requested
        if ($simplified) {
            $result = array_map(
                fn($code, $details) => ['code' => $code, 'name' => $details['name']],
                array_keys($result),
                $result
            );
            $result = array_values($result);
        }

        self::$cache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Get all cities for a country.
     *
     * @param string $countryCode Country code (e.g., 'NG')
     * @param bool $simplified Return only code and name if true.
     * @return array All cities for the country.
     */
    public static function getAll(string $countryCode, bool $simplified = false): array
    {
        $countryCode = strtoupper($countryCode);
        if (!isset(self::$cities[$countryCode])) {
            return [];
        }

        if ($simplified) {
            return array_map(
                fn($code, $details) => ['code' => $code, 'name' => $details['name']],
                array_keys(self::$cities[$countryCode]),
                self::$cities[$countryCode]
            );
        }
        return self::$cities[$countryCode];
    }

    /**
     * Get cities by state.
     *
     * @param string $countryCode Country code (e.g., 'NG')
     * @param string $state State name
     * @param bool $simplified Return only code and name if true.
     * @return array Cities in the specified state.
     */
    public static function getByState(string $countryCode, string $state, bool $simplified = false): array
    {
        $countryCode = strtoupper($countryCode);
        if (!isset(self::$cities[$countryCode])) {
            return [];
        }

        $result = array_filter(
            self::$cities[$countryCode],
            fn($city) => $city['state'] === $state
        );

        if ($simplified) {
            return array_map(
                fn($code, $details) => ['code' => $code, 'name' => $details['name']],
                array_keys($result),
                $result
            );
        }

        return $result;
    }

    /**
     * Get a single city by code.
     *
     * @param string $countryCode Country code (e.g., 'NG')
     * @param string $code The city code to look up.
     * @return array|null City details or null if not found.
     */
    public static function getCityByCode(string $countryCode, string $code): ?array
    {
        $countryCode = strtoupper($countryCode);
        return self::$cities[$countryCode][strtoupper($code)] ?? null;
    }

    /**
     * Check if a city code exists in a country.
     *
     * @param string $countryCode Country code (e.g., 'NG')
     * @param string $code The city code to check.
     * @return bool True if the city exists, false otherwise.
     */
    public static function exists(string $countryCode, string $code): bool
    {
        $countryCode = strtoupper($countryCode);
        return isset(self::$cities[$countryCode][strtoupper($code)]);
    }

    /**
     * Get all available states for a country.
     *
     * @param string $countryCode Country code (e.g., 'NG')
     * @return array List of unique states.
     */
    public static function getStates(string $countryCode): array
    {
        $countryCode = strtoupper($countryCode);
        if (!isset(self::$cities[$countryCode])) {
            return [];
        }

        $states = array_unique(array_column(self::$cities[$countryCode], 'state'));
        sort($states);
        return $states;
    }

    /**
     * Search cities by name (case-insensitive partial match).
     *
     * @param string $countryCode Country code (e.g., 'NG')
     * @param string $query Search query.
     * @param bool $simplified Return only code and name if true.
     * @return array Matching cities.
     */
    public static function searchByName(string $countryCode, string $query, bool $simplified = false): array
    {
        $countryCode = strtoupper($countryCode);
        if (!isset(self::$cities[$countryCode])) {
            return [];
        }

        $query = strtolower($query);
        $result = array_filter(
            self::$cities[$countryCode],
            fn($city) => strpos(strtolower($city['name']), $query) !== false
        );

        if ($simplified) {
            return array_map(
                fn($code, $details) => ['code' => $code, 'name' => $details['name']],
                array_keys($result),
                $result
            );
        }

        return $result;
    }

    /**
     * Clear the internal cache.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}