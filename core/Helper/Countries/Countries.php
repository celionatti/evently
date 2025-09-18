<?php

declare(strict_types=1);

namespace Trees\Helper\Countries;


/**
 * =======================================
 * ***************************************
 * ======== Trees Countries Class ========
 * ***************************************
 * Provides country data and filtering capabilities.
 *
 * Features:
 * - Comprehensive country data with ISO codes, names, and continents
 * - Filtering capabilities by continent, inclusion/exclusion lists
 * - Data validation and type safety
 * - Immutable operations (methods return new arrays rather than modifying state)
 * - Caching for better performance
 * =======================================
 */

class Countries
{
    /**
     * @var array Complete list of countries with their details
     */
    private static $countries = [
        // Africa
        'DZ' => ['name' => 'Algeria', 'continent' => 'Africa'],
        'AO' => ['name' => 'Angola', 'continent' => 'Africa'],
        'BJ' => ['name' => 'Benin', 'continent' => 'Africa'],
        'BW' => ['name' => 'Botswana', 'continent' => 'Africa'],
        'BF' => ['name' => 'Burkina Faso', 'continent' => 'Africa'],
        'BI' => ['name' => 'Burundi', 'continent' => 'Africa'],
        'CM' => ['name' => 'Cameroon', 'continent' => 'Africa'],
        'CV' => ['name' => 'Cape Verde', 'continent' => 'Africa'],
        'TD' => ['name' => 'Chad', 'continent' => 'Africa'],
        'KM' => ['name' => 'Comoros', 'continent' => 'Africa'],
        'CI' => ['name' => 'Ivory Coast', 'continent' => 'Africa'],
        'DJ' => ['name' => 'Djibouti', 'continent' => 'Africa'],
        'EG' => ['name' => 'Egypt', 'continent' => 'Africa'],
        'GQ' => ['name' => 'Equatorial Guinea', 'continent' => 'Africa'],
        'ER' => ['name' => 'Eritrea', 'continent' => 'Africa'],
        'SZ' => ['name' => 'Eswatini', 'continent' => 'Africa'],
        'ET' => ['name' => 'Ethiopia', 'continent' => 'Africa'],
        'GA' => ['name' => 'Gabon', 'continent' => 'Africa'],
        'GM' => ['name' => 'Gambia', 'continent' => 'Africa'],
        'GH' => ['name' => 'Ghana', 'continent' => 'Africa'],
        'GN' => ['name' => 'Guinea', 'continent' => 'Africa'],
        'GW' => ['name' => 'Guinea-Bissau', 'continent' => 'Africa'],
        'KE' => ['name' => 'Kenya', 'continent' => 'Africa'],
        'LS' => ['name' => 'Lesotho', 'continent' => 'Africa'],
        'LR' => ['name' => 'Liberia', 'continent' => 'Africa'],
        'LY' => ['name' => 'Libya', 'continent' => 'Africa'],
        'MG' => ['name' => 'Madagascar', 'continent' => 'Africa'],
        'MW' => ['name' => 'Malawi', 'continent' => 'Africa'],
        'ML' => ['name' => 'Mali', 'continent' => 'Africa'],
        'MR' => ['name' => 'Mauritania', 'continent' => 'Africa'],
        'MU' => ['name' => 'Mauritius', 'continent' => 'Africa'],
        'MA' => ['name' => 'Morocco', 'continent' => 'Africa'],
        'MZ' => ['name' => 'Mozambique', 'continent' => 'Africa'],
        'NA' => ['name' => 'Namibia', 'continent' => 'Africa'],
        'NE' => ['name' => 'Niger', 'continent' => 'Africa'],
        'NG' => ['name' => 'Nigeria', 'continent' => 'Africa'],
        'RW' => ['name' => 'Rwanda', 'continent' => 'Africa'],
        'ST' => ['name' => 'São Tomé and Príncipe', 'continent' => 'Africa'],
        'SN' => ['name' => 'Senegal', 'continent' => 'Africa'],
        'SC' => ['name' => 'Seychelles', 'continent' => 'Africa'],
        'SL' => ['name' => 'Sierra Leone', 'continent' => 'Africa'],
        'SO' => ['name' => 'Somalia', 'continent' => 'Africa'],
        'ZA' => ['name' => 'South Africa', 'continent' => 'Africa'],
        'SS' => ['name' => 'South Sudan', 'continent' => 'Africa'],
        'SD' => ['name' => 'Sudan', 'continent' => 'Africa'],
        'TZ' => ['name' => 'Tanzania', 'continent' => 'Africa'],
        'TG' => ['name' => 'Togo', 'continent' => 'Africa'],
        'TN' => ['name' => 'Tunisia', 'continent' => 'Africa'],
        'UG' => ['name' => 'Uganda', 'continent' => 'Africa'],
        'ZM' => ['name' => 'Zambia', 'continent' => 'Africa'],
        'ZW' => ['name' => 'Zimbabwe', 'continent' => 'Africa'],

        // Asia
        'AF' => ['name' => 'Afghanistan', 'continent' => 'Asia'],
        'AM' => ['name' => 'Armenia', 'continent' => 'Asia'],
        'AZ' => ['name' => 'Azerbaijan', 'continent' => 'Asia'],
        'BH' => ['name' => 'Bahrain', 'continent' => 'Asia'],
        'BD' => ['name' => 'Bangladesh', 'continent' => 'Asia'],
        'BT' => ['name' => 'Bhutan', 'continent' => 'Asia'],
        'BN' => ['name' => 'Brunei', 'continent' => 'Asia'],
        'KH' => ['name' => 'Cambodia', 'continent' => 'Asia'],
        'CN' => ['name' => 'China', 'continent' => 'Asia'],
        'CY' => ['name' => 'Cyprus', 'continent' => 'Asia'],
        'IN' => ['name' => 'India', 'continent' => 'Asia'],
        'ID' => ['name' => 'Indonesia', 'continent' => 'Asia'],
        'IR' => ['name' => 'Iran', 'continent' => 'Asia'],
        'IQ' => ['name' => 'Iraq', 'continent' => 'Asia'],
        'IL' => ['name' => 'Israel', 'continent' => 'Asia'],
        'JP' => ['name' => 'Japan', 'continent' => 'Asia'],
        'JO' => ['name' => 'Jordan', 'continent' => 'Asia'],
        'KZ' => ['name' => 'Kazakhstan', 'continent' => 'Asia'],
        'KW' => ['name' => 'Kuwait', 'continent' => 'Asia'],
        'KG' => ['name' => 'Kyrgyzstan', 'continent' => 'Asia'],
        'LA' => ['name' => 'Laos', 'continent' => 'Asia'],
        'LB' => ['name' => 'Lebanon', 'continent' => 'Asia'],
        'MY' => ['name' => 'Malaysia', 'continent' => 'Asia'],
        'MV' => ['name' => 'Maldives', 'continent' => 'Asia'],
        'MN' => ['name' => 'Mongolia', 'continent' => 'Asia'],
        'MM' => ['name' => 'Myanmar', 'continent' => 'Asia'],
        'NP' => ['name' => 'Nepal', 'continent' => 'Asia'],
        'OM' => ['name' => 'Oman', 'continent' => 'Asia'],
        'PK' => ['name' => 'Pakistan', 'continent' => 'Asia'],
        'PH' => ['name' => 'Philippines', 'continent' => 'Asia'],
        'QA' => ['name' => 'Qatar', 'continent' => 'Asia'],
        'SA' => ['name' => 'Saudi Arabia', 'continent' => 'Asia'],
        'SG' => ['name' => 'Singapore', 'continent' => 'Asia'],
        'KR' => ['name' => 'South Korea', 'continent' => 'Asia'],
        'LK' => ['name' => 'Sri Lanka', 'continent' => 'Asia'],
        'SY' => ['name' => 'Syria', 'continent' => 'Asia'],
        'TJ' => ['name' => 'Tajikistan', 'continent' => 'Asia'],
        'TH' => ['name' => 'Thailand', 'continent' => 'Asia'],
        'TR' => ['name' => 'Turkey', 'continent' => 'Asia'],
        'TM' => ['name' => 'Turkmenistan', 'continent' => 'Asia'],
        'AE' => ['name' => 'United Arab Emirates', 'continent' => 'Asia'],
        'UZ' => ['name' => 'Uzbekistan', 'continent' => 'Asia'],
        'VN' => ['name' => 'Vietnam', 'continent' => 'Asia'],
        'YE' => ['name' => 'Yemen', 'continent' => 'Asia'],

        // Europe
        'AL' => ['name' => 'Albania', 'continent' => 'Europe'],
        'AD' => ['name' => 'Andorra', 'continent' => 'Europe'],
        'AT' => ['name' => 'Austria', 'continent' => 'Europe'],
        'BY' => ['name' => 'Belarus', 'continent' => 'Europe'],
        'BE' => ['name' => 'Belgium', 'continent' => 'Europe'],
        'BA' => ['name' => 'Bosnia and Herzegovina', 'continent' => 'Europe'],
        'BG' => ['name' => 'Bulgaria', 'continent' => 'Europe'],
        'HR' => ['name' => 'Croatia', 'continent' => 'Europe'],
        // 'CY' => ['name' => 'Cyprus', 'continent' => 'Europe'],
        'CZ' => ['name' => 'Czech Republic', 'continent' => 'Europe'],
        'DK' => ['name' => 'Denmark', 'continent' => 'Europe'],
        'EE' => ['name' => 'Estonia', 'continent' => 'Europe'],
        'FI' => ['name' => 'Finland', 'continent' => 'Europe'],
        'FR' => ['name' => 'France', 'continent' => 'Europe'],
        'GE' => ['name' => 'Georgia', 'continent' => 'Europe'],
        'DE' => ['name' => 'Germany', 'continent' => 'Europe'],
        'GR' => ['name' => 'Greece', 'continent' => 'Europe'],
        'HU' => ['name' => 'Hungary', 'continent' => 'Europe'],
        'IS' => ['name' => 'Iceland', 'continent' => 'Europe'],
        'IE' => ['name' => 'Ireland', 'continent' => 'Europe'],
        'IT' => ['name' => 'Italy', 'continent' => 'Europe'],
        'XK' => ['name' => 'Kosovo', 'continent' => 'Europe'],
        'LV' => ['name' => 'Latvia', 'continent' => 'Europe'],
        'LI' => ['name' => 'Liechtenstein', 'continent' => 'Europe'],
        'LT' => ['name' => 'Lithuania', 'continent' => 'Europe'],
        'LU' => ['name' => 'Luxembourg', 'continent' => 'Europe'],
        'MT' => ['name' => 'Malta', 'continent' => 'Europe'],
        'MD' => ['name' => 'Moldova', 'continent' => 'Europe'],
        'MC' => ['name' => 'Monaco', 'continent' => 'Europe'],
        'ME' => ['name' => 'Montenegro', 'continent' => 'Europe'],
        'NL' => ['name' => 'Netherlands', 'continent' => 'Europe'],
        'MK' => ['name' => 'North Macedonia', 'continent' => 'Europe'],
        'NO' => ['name' => 'Norway', 'continent' => 'Europe'],
        'PL' => ['name' => 'Poland', 'continent' => 'Europe'],
        'PT' => ['name' => 'Portugal', 'continent' => 'Europe'],
        'RO' => ['name' => 'Romania', 'continent' => 'Europe'],
        'RU' => ['name' => 'Russia', 'continent' => 'Europe'],
        'SM' => ['name' => 'San Marino', 'continent' => 'Europe'],
        'RS' => ['name' => 'Serbia', 'continent' => 'Europe'],
        'SK' => ['name' => 'Slovakia', 'continent' => 'Europe'],
        'SI' => ['name' => 'Slovenia', 'continent' => 'Europe'],
        'ES' => ['name' => 'Spain', 'continent' => 'Europe'],
        'SE' => ['name' => 'Sweden', 'continent' => 'Europe'],
        'CH' => ['name' => 'Switzerland', 'continent' => 'Europe'],
        'UA' => ['name' => 'Ukraine', 'continent' => 'Europe'],
        'GB' => ['name' => 'United Kingdom', 'continent' => 'Europe'],
        'VA' => ['name' => 'Vatican City', 'continent' => 'Europe'],

        // North America
        'AG' => ['name' => 'Antigua and Barbuda', 'continent' => 'North America'],
        'BS' => ['name' => 'Bahamas', 'continent' => 'North America'],
        'BB' => ['name' => 'Barbados', 'continent' => 'North America'],
        'BZ' => ['name' => 'Belize', 'continent' => 'North America'],
        'CA' => ['name' => 'Canada', 'continent' => 'North America'],
        'CR' => ['name' => 'Costa Rica', 'continent' => 'North America'],
        'CU' => ['name' => 'Cuba', 'continent' => 'North America'],
        'DM' => ['name' => 'Dominica', 'continent' => 'North America'],
        'DO' => ['name' => 'Dominican Republic', 'continent' => 'North America'],
        'SV' => ['name' => 'El Salvador', 'continent' => 'North America'],
        'GD' => ['name' => 'Grenada', 'continent' => 'North America'],
        'GT' => ['name' => 'Guatemala', 'continent' => 'North America'],
        'HT' => ['name' => 'Haiti', 'continent' => 'North America'],
        'HN' => ['name' => 'Honduras', 'continent' => 'North America'],
        'JM' => ['name' => 'Jamaica', 'continent' => 'North America'],
        'MX' => ['name' => 'Mexico', 'continent' => 'North America'],
        'NI' => ['name' => 'Nicaragua', 'continent' => 'North America'],
        'PA' => ['name' => 'Panama', 'continent' => 'North America'],
        'KN' => ['name' => 'Saint Kitts and Nevis', 'continent' => 'North America'],
        'LC' => ['name' => 'Saint Lucia', 'continent' => 'North America'],
        'VC' => ['name' => 'Saint Vincent and the Grenadines', 'continent' => 'North America'],
        'TT' => ['name' => 'Trinidad and Tobago', 'continent' => 'North America'],
        'US' => ['name' => 'United States', 'continent' => 'North America'],

        // South America
        'AR' => ['name' => 'Argentina', 'continent' => 'South America'],
        'BO' => ['name' => 'Bolivia', 'continent' => 'South America'],
        'BR' => ['name' => 'Brazil', 'continent' => 'South America'],
        'CL' => ['name' => 'Chile', 'continent' => 'South America'],
        'CO' => ['name' => 'Colombia', 'continent' => 'South America'],
        'EC' => ['name' => 'Ecuador', 'continent' => 'South America'],
        'GY' => ['name' => 'Guyana', 'continent' => 'South America'],
        'PY' => ['name' => 'Paraguay', 'continent' => 'South America'],
        'PE' => ['name' => 'Peru', 'continent' => 'South America'],
        'SR' => ['name' => 'Suriname', 'continent' => 'South America'],
        'UY' => ['name' => 'Uruguay', 'continent' => 'South America'],
        'VE' => ['name' => 'Venezuela', 'continent' => 'South America'],

        // Oceania
        'AU' => ['name' => 'Australia', 'continent' => 'Oceania'],
        'FJ' => ['name' => 'Fiji', 'continent' => 'Oceania'],
        'KI' => ['name' => 'Kiribati', 'continent' => 'Oceania'],
        'MH' => ['name' => 'Marshall Islands', 'continent' => 'Oceania'],
        'FM' => ['name' => 'Micronesia', 'continent' => 'Oceania'],
        'NR' => ['name' => 'Nauru', 'continent' => 'Oceania'],
        'NZ' => ['name' => 'New Zealand', 'continent' => 'Oceania'],
        'PW' => ['name' => 'Palau', 'continent' => 'Oceania'],
        'PG' => ['name' => 'Papua New Guinea', 'continent' => 'Oceania'],
        'WS' => ['name' => 'Samoa', 'continent' => 'Oceania'],
        'SB' => ['name' => 'Solomon Islands', 'continent' => 'Oceania'],
        'TO' => ['name' => 'Tonga', 'continent' => 'Oceania'],
        'TV' => ['name' => 'Tuvalu', 'continent' => 'Oceania'],
        'VU' => ['name' => 'Vanuatu', 'continent' => 'Oceania'],
    ];

    /**
     * @var array Cache for filtered results
     */
    private static $cache = [];

    /**
     * Get countries with optional filtering and output format.
     *
     * @param array $include Only include these countries by code or continent.
     * @param array $exclude Exclude these countries by code or continent.
     * @param bool $simplified Return only code and name if true.
     * @return array Filtered and formatted countries.
     */
    public static function getCountries(array $include = [], array $exclude = [], bool $simplified = false): array
    {
        $cacheKey = md5(serialize([$include, $exclude, $simplified]));

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $result = self::$countries;

        // Apply includes if specified
        if (!empty($include)) {
            $included = [];
            foreach ($include as $item) {
                if (isset($result[$item])) {
                    $included[$item] = $result[$item];
                } else {
                    // Assume it's a continent if not a country code
                    $continentCountries = array_filter($result, fn($country) => $country['continent'] === $item);
                    $included = array_merge($included, $continentCountries);
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
                    // Assume it's a continent if not a country code
                    $result = array_filter($result, fn($country) => $country['continent'] !== $item);
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
            $result = array_values($result); // Reset keys for simplified output
        }

        self::$cache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Get all countries.
     *
     * @param bool $simplified Return only code and name if true.
     * @return array All countries.
     */
    public static function getAll(bool $simplified = false): array
    {
        if ($simplified) {
            return array_map(
                fn($code, $details) => ['code' => $code, 'name' => $details['name']],
                array_keys(self::$countries),
                self::$countries
            );
        }
        return self::$countries;
    }

    /**
     * Get countries by continent.
     *
     * @param string $continent The continent to filter by.
     * @param bool $simplified Return only code and name if true.
     * @return array Countries in the specified continent.
     * @throws \InvalidArgumentException If continent is invalid.
     */
    public static function getByContinent(string $continent, bool $simplified = false): array
    {
        $continents = ['Africa', 'Asia', 'Europe', 'North America', 'South America', 'Oceania', 'Antarctica'];
        if (!in_array($continent, $continents, true)) {
            throw new \InvalidArgumentException("Invalid continent: {$continent}");
        }

        $result = array_filter(self::$countries, fn($country) => $country['continent'] === $continent);

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
     * Get a single country by code.
     *
     * @param string $code The country code to look up.
     * @return array|null Country details or null if not found.
     */
    public static function getCountryByCode(string $code): ?array
    {
        return self::$countries[strtoupper($code)] ?? null;
    }

    /**
     * Check if a country code exists.
     *
     * @param string $code The country code to check.
     * @return bool True if the country exists, false otherwise.
     */
    public static function exists(string $code): bool
    {
        return isset(self::$countries[strtoupper($code)]);
    }

    /**
     * Get all available continents.
     *
     * @return array List of unique continents.
     */
    public static function getContinents(): array
    {
        $continents = array_unique(array_column(self::$countries, 'continent'));
        sort($continents);
        return $continents;
    }

    /**
     * Search countries by name (case-insensitive partial match).
     *
     * @param string $query Search query.
     * @param bool $simplified Return only code and name if true.
     * @return array Matching countries.
     */
    public static function searchByName(string $query, bool $simplified = false): array
    {
        $query = strtolower($query);
        $result = array_filter(self::$countries, fn($country) => strpos(strtolower($country['name']), $query) !== false);

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
     * Get country name by code.
     *
     * @param string $code The country code.
     * @return string|null Country name or null if not found.
     */
    public static function getName(string $code): ?string
    {
        return self::$countries[strtoupper($code)]['name'] ?? null;
    }

    /**
     * Get country continent by code.
     *
     * @param string $code The country code.
     * @return string|null Continent name or null if not found.
     */
    public static function getContinent(string $code): ?string
    {
        return self::$countries[strtoupper($code)]['continent'] ?? null;
    }

    /**
     * Clear the internal cache.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}