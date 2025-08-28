<?php

declare(strict_types=1);

namespace Trees\Helper\Utils;

/**
 * =========================================
 * *****************************************
 * ========== Trees DeviceInfo Class =======
 * *****************************************
 * A comprehensive utility class for detecting device information,
 * including IP address, operating system, browser, and device type.
 *
 * Features:
 * - IP address detection with proxy support
 * - Detailed OS detection
 * - Browser detection with version parsing
 * - Device type classification
 * - Caching for improved performance
 * - Comprehensive error handling
 * =========================================
 */

class DeviceInfo
{
    private static ?array $cache = null;

    /**
     * Retrieve user agent string with validation
     */
    private static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Retrieve client IP address with enhanced detection and validation
     *
     * @return string The client IP address or 'UNKNOWN' if not detectable
     */
    public static function getIP(): string
    {
        if (self::$cache !== null && isset(self::$cache['ip'])) {
            return self::$cache['ip'];
        }

        $ipHeaders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
            } elseif (!empty(getenv($header))) {
                $ip = getenv($header);
            } else {
                continue;
            }

            $ip = trim($ip);

            // Handle comma-separated IP lists (common in X-Forwarded-For)
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);

            // Validate IP address
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                self::$cache['ip'] = $ip;
                return $ip;
            }
        }

        return 'UNKNOWN';
    }

    /**
     * Comprehensive OS detection with updated patterns
     *
     * @return array OS information including type, name, and user agent
     */
    public static function getOS(): array
    {
        if (self::$cache !== null && isset(self::$cache['os'])) {
            return self::$cache['os'];
        }

        $userAgent = self::getUserAgent();

        $osPatterns = [
            'Windows' => [
                '/windows nt 11/i' => 'Windows 11',
                '/windows nt 10/i' => 'Windows 10',
                '/windows nt 6.3/i' => 'Windows 8.1',
                '/windows nt 6.2/i' => 'Windows 8',
                '/windows nt 6.1/i' => 'Windows 7',
                '/windows nt 6.0/i' => 'Windows Vista',
                '/windows nt 5.2/i' => 'Windows Server 2003',
                '/windows nt 5.1/i' => 'Windows XP',
                '/windows phone/i' => 'Windows Phone',
            ],
            'Mac' => [
                '/macintosh|mac os x|mac_powerpc/i' => 'macOS',
            ],
            'Linux' => [
                '/linux/i' => 'Linux',
                '/ubuntu/i' => 'Ubuntu',
                '/fedora/i' => 'Fedora',
                '/debian/i' => 'Debian',
                '/centos/i' => 'CentOS',
                '/redhat/i' => 'Red Hat',
            ],
            'Mobile' => [
                '/android/i' => 'Android',
                '/iphone|ipod/i' => 'iOS',
                '/ipad/i' => 'iPadOS',
                '/blackberry/i' => 'BlackBerry OS',
                '/webos/i' => 'webOS',
            ],
            'BSD' => [
                '/bsd/i' => 'BSD',
            ],
            'Unix' => [
                '/sunos|solaris/i' => 'SunOS/Solaris',
            ],
            'ChromeOS' => [
                '/cros/i' => 'ChromeOS',
            ],
        ];

        foreach ($osPatterns as $osType => $patterns) {
            foreach ($patterns as $regex => $name) {
                if (preg_match($regex, $userAgent)) {
                    $result = [
                        'type' => $osType,
                        'name' => $name,
                        'userAgent' => $userAgent
                    ];
                    self::$cache['os'] = $result;
                    return $result;
                }
            }
        }

        $result = [
            'type' => 'Unknown',
            'name' => 'Unknown OS',
            'userAgent' => $userAgent
        ];
        self::$cache['os'] = $result;
        return $result;
    }

    /**
     * Advanced browser detection with version parsing
     *
     * @return array Browser information including name, version, and user agent
     */
    public static function getBrowser(): array
    {
        if (self::$cache !== null && isset(self::$cache['browser'])) {
            return self::$cache['browser'];
        }

        $userAgent = self::getUserAgent();

        $browserPatterns = [
            'Chrome' => '/chrome|crios/i',
            'Firefox' => '/firefox|fxios/i',
            'Safari' => '/safari/i',
            'Opera' => '/opera|opr/i',
            'Edge' => '/edge|edg|edga|edgios|edg/i',
            'Internet Explorer' => '/msie|trident/i',
            'Brave' => '/brave/i',
            'Vivaldi' => '/vivaldi/i',
            'Samsung Browser' => '/samsungbrowser/i',
            'UC Browser' => '/ucbrowser/i',
            'DuckDuckGo' => '/duckduckgo/i',
        ];

        foreach ($browserPatterns as $name => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $version = 'Unknown';

                // Extract version based on browser type
                switch ($name) {
                    case 'Chrome':
                        preg_match('/chrome\/([\d.]+)/i', $userAgent, $matches);
                        break;
                    case 'Firefox':
                        preg_match('/firefox\/([\d.]+)/i', $userAgent, $matches);
                        break;
                    case 'Safari':
                        preg_match('/version\/([\d.]+)/i', $userAgent, $matches);
                        break;
                    case 'Opera':
                        preg_match('/(?:opera|opr)\/([\d.]+)/i', $userAgent, $matches);
                        break;
                    case 'Edge':
                        preg_match('/edge\/([\d.]+)/i', $userAgent, $matches);
                        break;
                    case 'Internet Explorer':
                        preg_match('/(?:msie |rv:)([\d.]+)/i', $userAgent, $matches);
                        break;
                    default:
                        preg_match('/version\/([\d.]+)/i', $userAgent, $matches);
                }

                if (!empty($matches[1])) {
                    $version = $matches[1];
                }

                $result = [
                    'name' => $name,
                    'version' => $version,
                    'userAgent' => $userAgent
                ];
                self::$cache['browser'] = $result;
                return $result;
            }
        }

        $result = [
            'name' => 'Unknown',
            'version' => 'Unknown',
            'userAgent' => $userAgent
        ];
        self::$cache['browser'] = $result;
        return $result;
    }

    /**
     * Advanced device type detection with more patterns
     *
     * @return array Device information including type, details, and user agent
     */
    public static function getDevice(): array
    {
        if (self::$cache !== null && isset(self::$cache['device'])) {
            return self::$cache['device'];
        }

        $userAgent = strtolower(self::getUserAgent());

        $devicePatterns = [
            'Mobile' => [
                'phones' => [
                    'android', 'webos', 'iphone', 'ipod',
                    'blackberry', 'windows phone', 'phone',
                    'mobile', 'palm', 'pocket', 'smartphone'
                ],
                'mini_browsers' => [
                    'up.browser', 'up.link', 'mmp', 'symbian',
                    'midp', 'wap', 'vodafone', 'o2', 'pocket',
                    'kindle', 'mobile', 'pda', 'psp', 'treo'
                ]
            ],
            'Tablet' => [
                'tablet', 'ipad', 'playbook', 'kindle', 'silk',
                'tab', 'gt-p', 'sm-t', 'nexus 7', 'nexus 10',
                'xoom', 'sch-i800', 'a100', 'a500', 'a510'
            ],
            'TV' => [
                'tv', 'smarttv', 'googletv', 'appletv',
                'hbbtv', 'netcast', 'viera', 'roku',
                'boxee', 'roku', 'crkey'
            ],
            'Console' => [
                'xbox', 'playstation', 'nintendo', 'wii',
                'switch', '3ds', 'psp', 'psvita'
            ],
            'Wearable' => [
                'watch', 'wear', 'galaxy watch', 'apple watch',
                'gear', 'fitbit', 'mi band', 'androidwear'
            ],
            'Bot' => [
                'bot', 'crawl', 'spider', 'slurp', 'search',
                'archiver', 'facebookexternalhit', 'scraper'
            ],
            'Desktop' => [
                'windows', 'macintosh', 'linux', 'unix',
                'bsd', 'sunos', 'solaris', 'desktop'
            ]
        ];

        // Check for bots first
        foreach ($devicePatterns['Bot'] as $botPattern) {
            if (strpos($userAgent, $botPattern) !== false) {
                $result = [
                    'type' => 'Bot',
                    'details' => $botPattern,
                    'userAgent' => self::getUserAgent()
                ];
                self::$cache['device'] = $result;
                return $result;
            }
        }

        // Check for tablet
        foreach ($devicePatterns['Tablet'] as $tabletPattern) {
            if (strpos($userAgent, $tabletPattern) !== false) {
                $result = [
                    'type' => 'Tablet',
                    'details' => $tabletPattern,
                    'userAgent' => self::getUserAgent()
                ];
                self::$cache['device'] = $result;
                return $result;
            }
        }

        // Check for mobile
        foreach ($devicePatterns['Mobile']['phones'] as $mobilePattern) {
            if (strpos($userAgent, $mobilePattern) !== false) {
                $result = [
                    'type' => 'Mobile',
                    'details' => $mobilePattern,
                    'userAgent' => self::getUserAgent()
                ];
                self::$cache['device'] = $result;
                return $result;
            }
        }

        // Check for other device types
        foreach ($devicePatterns as $type => $patterns) {
            if ($type === 'Mobile' || $type === 'Tablet' || $type === 'Bot') continue;

            $checkPatterns = is_array($patterns[0] ?? null) ? $patterns['phones'] ?? $patterns : $patterns;

            foreach ($checkPatterns as $pattern) {
                if (strpos($userAgent, $pattern) !== false) {
                    $result = [
                        'type' => $type,
                        'details' => $pattern,
                        'userAgent' => self::getUserAgent()
                    ];
                    self::$cache['device'] = $result;
                    return $result;
                }
            }
        }

        $result = [
            'type' => 'Unknown',
            'details' => 'Unable to determine device type',
            'userAgent' => self::getUserAgent()
        ];
        self::$cache['device'] = $result;
        return $result;
    }

    /**
     * Get comprehensive device information with caching
     *
     * @return array All device information including IP, OS, browser, and device type
     */
    public static function getDeviceInfo(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        self::$cache = [
            'ip' => self::getIP(),
            'os' => self::getOS(),
            'browser' => self::getBrowser(),
            'device' => self::getDevice()
        ];

        return self::$cache;
    }

    /**
     * Clear the cached device information
     */
    public static function clearCache(): void
    {
        self::$cache = null;
    }
}