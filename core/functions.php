<?php

declare(strict_types=1);

use Trees\Uuid\TreesUuidv1;
use Trees\Http\Request;
use Trees\Session\Session;

/**
 * =======================================
 * ***************************************
 * ========== Trees Functions ============
 * ***************************************
 * =======================================
 */

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('detect_environment')) {
    /**
     * Detect current environment
     *
     * @return string Detected environment
     */
    function detect_environment(): string
    {
        if (isset($_SERVER['APP_ENV'])) {
            return strtolower($_SERVER['APP_ENV']);
        }

        if (isset($_ENV['APP_ENV'])) {
            return strtolower($_ENV['APP_ENV']);
        }

        if ($_SERVER['HTTP_HOST'] ?? null === 'localhost') {
            return 'development';
        }

        return 'production';
    }
}

if (!function_exists('active_nav')) {
    /**
     * Determines if a navigation item should be marked as active based on URL segments.
     *
     * @param int|array $position The 1-based index(es) of URI segments to check
     * @param array|string $value The value(s) to match against
     * @param string|null $uri Optional URI to check (defaults to current request URI)
     * @param bool $exactMatch Whether to require exact segment count match
     * @return bool Returns true if the segment(s) match the value(s)
     * @throws \InvalidArgumentException If position and value arrays have unequal length
     */
    function active_nav(int|array $position, array|string $value, ?string $uri = null, bool $exactMatch = false): bool
    {
        // Get and normalize the URI path
        $uriToCheck = $uri ?? $_SERVER['REQUEST_URI'] ?? '/';
        $uriPath = parse_url($uriToCheck, PHP_URL_PATH) ?? '/';

        // Special case: home/root URL
        if ($uriPath === '/' || $uriPath === '') {
            if (is_array($position)) {
                return false; // Home can't match multiple positions
            }
            return $position === 1 && ($value === '/' || $value === '');
        }

        $segments = array_values(array_filter(explode('/', trim($uriPath, '/')), 'strlen'));

        // Handle multiple position checks
        if (is_array($position)) {
            if (count($position) !== count($value)) {
                throw new \InvalidArgumentException('Position and value arrays must have equal length');
            }

            foreach ($position as $i => $pos) {
                if (!isset($segments[$pos - 1])) {
                    return false;
                }
                $currentVal = is_array($value[$i]) ? $value[$i] : [$value[$i]];
                if (!in_array($segments[$pos - 1], $currentVal, true)) {
                    return false;
                }
            }
            return true;
        }

        // Check if position exists
        if (!isset($segments[$position - 1])) {
            return false;
        }

        // Handle exact match requirement
        if ($exactMatch && count($segments) !== $position) {
            return false;
        }

        $currentSegment = $segments[$position - 1];

        // Handle array of possible values
        if (is_array($value)) {
            return in_array($currentSegment, $value, true);
        }

        return $currentSegment === (string)$value;
    }
}

if (!function_exists('asset')) {
    /**
     * Generates a URL for an asset with advanced features
     *
     * @param string $path Relative path to the asset
     * @param array $options {
     *     @type bool   $version     Whether to add version query string (default: true)
     *     @type bool   $minify      Whether to use minified version (default: auto-detect)
     *     @type bool   $sri         Whether to generate SRI hash (default: false)
     *     @type string $environment Current environment (default: auto-detect)
     * }
     * @return string|array Full URL to the asset, or array with URL and SRI attributes
     *
     * @throws InvalidArgumentException If the asset type is not supported
     * @throws RuntimeException If the asset is not found
     */
    function asset(string $path, array $options = []): string|array
    {
        // Merge with default options
        $defaults = [
            'version' => true,
            'minify' => null, // auto-detect
            'sri' => false,
            'environment' => null,
        ];
        $options = array_merge($defaults, $options);

        // Environment detection
        $environment = $options['environment'] ?? detect_environment();

        // Define asset configuration
        static $assetConfig = [
            'images' => [
                'dir' => '/assets/img/',
                'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico'],
                'default' => 'default.jpg',
                'minify' => false, // Images typically don't get minified
                'cdn' => true
            ],
            'styles' => [
                'dir' => '/assets/css/',
                'extensions' => ['css'],
                'default' => null,
                'minify' => true,
                'cdn' => true
            ],
            'scripts' => [
                'dir' => '/assets/js/',
                'extensions' => ['js'],
                'default' => null,
                'minify' => true,
                'cdn' => true
            ],
            'fonts' => [
                'dir' => '/assets/fonts/',
                'extensions' => ['woff', 'woff2', 'ttf', 'eot', 'otf'],
                'default' => null,
                'minify' => false,
                'cdn' => true
            ],
            'downloads' => [
                'dir' => '/assets/downloads/',
                'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
                'default' => null,
                'minify' => false,
                'cdn' => false
            ]
        ];

        // CDN configuration
        static $cdnUrls = [
            'production' => 'https://cdn.yourdomain.com',
            'staging' => 'https://staging-cdn.yourdomain.com'
        ];

        // Determine asset type from file extension
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $type = null;

        foreach ($assetConfig as $assetType => $config) {
            if (in_array($extension, $config['extensions'])) {
                $type = $assetType;
                break;
            }
        }

        if ($type === null) {
            throw new InvalidArgumentException("Unsupported file type: {$extension}");
        }

        // Handle minification
        $minify = $options['minify'] ?? ($environment === 'production' && $assetConfig[$type]['minify']);
        if ($minify && !str_ends_with($path, '.min.' . $extension)) {
            $minPath = preg_replace('/(\.' . $extension . ')$/', '.min.$1', $path);
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $assetConfig[$type]['dir'] . $minPath)) {
                $path = $minPath;
            }
        }

        // Build filesystem path
        $fsPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') .
            $assetConfig[$type]['dir'] .
            ltrim($path, '/');

        // Check if file exists and is readable
        if (!is_readable($fsPath)) {
            if ($assetConfig[$type]['default'] !== null) {
                $path = $assetConfig[$type]['default'];
                $fsPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') .
                    $assetConfig[$type]['dir'] .
                    $assetConfig[$type]['default'];
            } else {
                throw new RuntimeException("Asset not found: {$path}");
            }
        }

        // Build base URL
        $useCdn = $assetConfig[$type]['cdn'] && isset($cdnUrls[$environment]);
        $baseUrl = $useCdn ? $cdnUrls[$environment] : ROOT_PATH;
        $url = $baseUrl . $assetConfig[$type]['dir'] . ltrim($path, '/');

        // Add version query string if enabled
        if ($options['version']) {
            $filemtime = filemtime($fsPath);
            $url .= '?v=' . substr(md5((string)$filemtime), 0, 8);
        }

        // Handle Subresource Integrity
        if ($options['sri'] && in_array($type, ['styles', 'scripts'])) {
            $hash = base64_encode(hash_file('sha384', $fsPath, true));
            $sriAttr = " integrity='sha384-{$hash}' crossorigin='anonymous'";

            if ($options['sri'] === true) {
                return [
                    'url' => $url,
                    'integrity' => "sha384-{$hash}",
                    'html' => "{$url}'{$sriAttr}"
                ];
            }
            return "{$url}'{$sriAttr}";
        }

        return $url;
    }
}

if (!function_exists('display_flash_message')) {
    function display_flash_message()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['__tre_flash_message'])) {
            $type = $_SESSION['__tre_flash_message']['type'];
            $message = $_SESSION['__tre_flash_message']['message'];

            echo "<div class=\"alert alert-{$type} alert-dismissible fade show text-center fixed-top\" style=\"position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;\">
                {$message}
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
              </div>";

            // Clear the flash message after displaying it
            unset($_SESSION['__tre_flash_message']);
        }
    }
}

if (!function_exists('uuid')) {
    function uuid()
    {
        return TreesUuidv1::generate();
    }
}

if (!function_exists('set_form_error')) {
    function set_form_error($value): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['__tre_form_error'] = $value;
    }
}

if (!function_exists('get_form_error')) {
    function get_form_error($value)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $message = $_SESSION['__tre_form_error'] ?? null;
        if ($message) {
            unset($_SESSION['__tre_form_error']);
        }
        return $message ?? [];
    }
}

if (!function_exists('set_form_data')) {
    function set_form_data(array $data): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION["__tre_form_data"] = $data;
        // Mark that we have new form data to display
        $_SESSION['__tre_form_data_show'] = true;
    }
}

if (!function_exists('get_form_data')) {
    function get_form_data(?string $key = null, $default = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['__tre_form_data_show']) || !$_SESSION['__tre_form_data_show']) {
            return $default;
        }

        // Mark that we've shown this data (so it won't show again)
        $_SESSION['__tre_form_data_show'] = false;

        if ($key === null) {
            return $_SESSION['__tre_form_data'] ?? $default;
        }

        return $_SESSION['__tre_form_data'][$key] ?? $default;
    }
}

if (!function_exists('remove_form_data')) {
    function remove_form_data(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['__tre_form_data'], $_SESSION['__tre_form_data_show']);
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve old form input (flash data - cleared after first access)
     *
     * @param string $key The input field name
     * @param mixed $default Default value if not found
     * @return mixed The stored value or default
     */
    function old(string $key, $default = '')
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if we should show the data (first request after set)
        if (!isset($_SESSION['__tre_form_data_show']) || !$_SESSION['__tre_form_data_show']) {
            return $default;
        }

        // Get the value if it exists
        $value = $_SESSION['__tre_form_data'][$key] ?? $default;

        // Mark that we've accessed this data (so it won't show again)
        $_SESSION['__tre_form_data_show'] = false;

        // Return the value, properly escaped for HTML output
        return $value;
    }
}

if (!function_exists('has_error')) {
    /**
     * Check if a field has validation error
     */
    function has_error(string $key): bool
    {
        return isset($_SESSION['__tre_form_error'][$key]);
    }
}

if (!function_exists('get_error')) {
    /**
     * Get and clear validation error messages (handles both string and array errors)
     *
     * @param string $key The error key to retrieve
     * @return string HTML formatted error message (cleared after retrieval)
     */
    function get_error(string $key): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['__tre_form_error'][$key])) {
            return '';
        }

        $errors = $_SESSION['__tre_form_error'][$key];

        // Clear the error after retrieval
        unset($_SESSION['__tre_form_error'][$key]);

        // If all errors are cleared, clean up the parent array
        if (empty($_SESSION['__tre_form_error'])) {
            unset($_SESSION['__tre_form_error']);
        }

        // Handle array of errors
        if (is_array($errors)) {
            return '<ul class="mb-0"><li>' . implode('</li><li>', array_map('htmlspecialchars', $errors)) . '</li></ul>';
        }

        // Handle single string error (with HTML escaping)
        return htmlspecialchars($errors);
    }
}

if (!function_exists('get_first_error')) {
    function get_first_error(string $key): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['__tre_form_error'][$key])) {
            return '';
        }

        $errors = $_SESSION['__tre_form_error'][$key];
        $firstError = '';

        if (is_array($errors)) {
            $firstError = array_shift($errors);
            $_SESSION['__tre_form_error'][$key] = $errors;

            // Remove key if no errors left
            if (empty($errors)) {
                unset($_SESSION['__tre_form_error'][$key]);
            }
        } else {
            $firstError = $errors;
            unset($_SESSION['__tre_form_error'][$key]);
        }

        // Clean up parent array if empty
        if (empty($_SESSION['__tre_form_error'])) {
            unset($_SESSION['__tre_form_error']);
        }

        return htmlspecialchars($firstError);
    }
}

if (!function_exists('url')) {
    /**
     * Generate a URL with optional query parameters
     *
     * @param string $path The base path/URL
     * @param array $params Optional query parameters
     * @param array $options Configuration options
     * @return string|null The generated URL or null on failure
     * @throws InvalidArgumentException If path is invalid
     */
    function url(string $path, array $params = [], array $options = []): ?string
    {
        // Validation
        if (empty($path) || !is_string($path)) {
            throw new \InvalidArgumentException('Path must be a non-empty string');
        }

        // Configuration with defaults
        $config = array_merge([
            'validate_url' => true,
            'encoding' => PHP_QUERY_RFC3986,
            'max_url_length' => 2048,
            'allowed_schemes' => ['http', 'https', ''],
            'sanitize_path' => true
        ], $options);

        try {
            // Sanitize path if requested
            if ($config['sanitize_path']) {
                $path = filter_var(trim($path), FILTER_SANITIZE_URL);
                if ($path === false) {
                    return null;
                }
            }

            // Validate URL structure if requested
            if ($config['validate_url'] && !empty($path)) {
                $parsedUrl = parse_url($path);
                if ($parsedUrl === false) {
                    return null;
                }

                // Check allowed schemes
                if (
                    isset($parsedUrl['scheme']) &&
                    !in_array($parsedUrl['scheme'], $config['allowed_schemes'], true)
                ) {
                    return null;
                }
            }

            // Build query string from parameters
            $finalUrl = $path;
            if (!empty($params)) {
                // Filter out null/empty values if desired
                $cleanParams = array_filter($params, function ($value) {
                    return $value !== null && $value !== '';
                });

                if (!empty($cleanParams)) {
                    $queryString = http_build_query($cleanParams, '', '&', $config['encoding']);
                    $separator = strpos($finalUrl, '?') === false ? '?' : '&';
                    $finalUrl = $finalUrl . $separator . $queryString;
                }
            }

            // Check maximum URL length
            if (strlen($finalUrl) > $config['max_url_length']) {
                error_log("Generated URL exceeds maximum length: " . strlen($finalUrl));
                return null;
            }

            return $finalUrl;
        } catch (Exception $e) {
            // Log error for debugging
            error_log("URL generation failed: " . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('getUrl')) {
    /**
     * Generate URL specifically for GET requests (backwards compatibility)
     *
     * @param string $path The base path/URL
     * @param array $params Optional query parameters
     * @return string|null The generated URL or null if not a GET request
     */
    function getUrl(string $path, array $params = []): ?string
    {
        // Check if the request method is GET
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($requestMethod !== 'GET') {
            return null;
        }

        return url($path, $params);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        $now = time();

        // Generate new token if none exists or if expired
        if (
            empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_expiry']) ||
            $now >= $_SESSION['csrf_token_expiry']
        ) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            // Set expiration to 1 minute from now
            $_SESSION['csrf_token_expiry'] = $now + 60;
        }

        return $_SESSION['csrf_token'];
    }
}

// Verify CSRF token
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token)
    {
        $now = time();

        // Check if token exists and hasn't expired
        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_expiry'])) {
            return false;
        }

        if ($now >= $_SESSION['csrf_token_expiry']) {
            // Clear expired token
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_expiry']);
            return false;
        }

        // Use hash_equals for timing attack prevention
        if (hash_equals($_SESSION['csrf_token'], $token)) {
            // Generate a new token after successful verification
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_expiry'] = $now + 60;
            return true;
        }

        return false;
    }
}

// Generate CSRF token HTML field
if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__, 1) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('format_currency')) {
    /**
     * Formats a numeric amount as a currency string with appropriate symbol and formatting
     *
     * @param float|string|int $amount The amount to format (can be numeric or string representation)
     * @param string $currencyCode ISO 4217 currency code (default: 'NGN')
     * @return string Formatted currency string or error message if invalid
     * @throws InvalidArgumentException If currency code is not supported
     */
    function format_currency($amount, string $currencyCode = 'NGN'): string
    {
        // Define currency symbols and formatting rules
        $currencyData = [
            'USD' => ['symbol' => '$', 'decimal_places' => 2],
            'NGN' => ['symbol' => '₦', 'decimal_places' => 2],
            'EUR' => ['symbol' => '€', 'decimal_places' => 2, 'symbol_position' => 'after'],
            // Add more currencies as needed
        ];

        // Validate currency code
        if (!array_key_exists($currencyCode, $currencyData)) {
            throw new InvalidArgumentException("Unsupported currency code: {$currencyCode}");
        }

        // Validate and convert amount
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Amount must be numeric');
        }

        $amount = (float)$amount;
        $currency = $currencyData[$currencyCode];
        $decimalPlaces = $currency['decimal_places'] ?? 2;

        // Format the number with appropriate decimal places and thousands separator
        $formattedAmount = number_format(
            $amount,
            $decimalPlaces,
            '.',       // Decimal separator
            ','        // Thousands separator
        );

        // Determine symbol position (before or after)
        $symbolPosition = $currency['symbol_position'] ?? 'before';

        return $symbolPosition === 'after'
            ? "{$formattedAmount} {$currency['symbol']}"
            : "{$currency['symbol']}{$formattedAmount}";
    }
}

if (!function_exists('get_image')) {
    function get_image(?string $path = null, string $default = ''): string
    {
        // If path is provided and file exists, return full URL
        if (!empty($path) && file_exists($path)) {
            return env("APP_URL") . '/' . ltrim($path, '/');
        }

        // If path is null/empty, return default if provided
        if (!empty($default)) {
            // Check if default is already a full URL (starts with http:// or https://)
            if (filter_var($default, FILTER_VALIDATE_URL)) {
                return $default;
            }
            // If it's a relative path, prepend APP_URL
            return env("APP_URL") . '/' . ltrim($default, '/');
        }

        // Return empty string if no default is provided
        return '';
    }
}

if (!function_exists('session')) {
    function session(): Session
    {
        return Session::getInstance();
    }
}

if (!function_exists('redirect')) {
    /**
     * Robust redirect method for MVC framework
     *
     * @param string $url URL to redirect to (can be relative or absolute)
     * @param int $statusCode HTTP status code (default: 302 Found)
     * @param bool $terminate Whether to terminate after redirect (default: true)
     * @return void
     */
    function redirect(string $url, int $statusCode = 302, bool $terminate = true): void
    {
        // Ensure URL is absolute
        if (!preg_match('/^https?:\/\//i', $url)) {
            $baseUrl = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST'], '/');
            $url = $baseUrl . '/' . ltrim($url, '/');
        }

        // Clear output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Set proper headers
        header("Location: $url", true, $statusCode);

        // For AJAX requests
        if (isAjaxRequest()) {
            header("Content-Type: application/json");
            echo json_encode(['redirect' => $url]);
            if ($terminate) {
                exit;
            }
            return;
        }

        // Optional: Add debug info in development
        if (env("APP_ENV") === 'development') {
            echo "Redirecting to: <a href=\"$url\">$url</a>";
        }

        if ($terminate) {
            exit;
        }
    }
}

if (!function_exists('isAjaxRequest')) {
    /**
     * Check if request is AJAX
     */
    function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
