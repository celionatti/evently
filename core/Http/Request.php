<?php

declare(strict_types=1);

namespace Trees\Http;

use Trees\Exception\TreesException;
use Trees\Validation\Validator;
use Trees\Helper\Support\UploadedFile;

/**
 * =======================================
 * ***************************************
 * ========= Trees Request Class =========
 * ***************************************
 * =======================================
 */

class Request
{
    private array $queryParams;
    private array $bodyParams;
    private array $rawBodyParams = []; // Store unsanitized body params
    private array $serverParams;
    private array $cookies;
    private array $files;
    private string $method;
    private string $path;
    private string $content;
    private array $headers;
    private array $errors = [];
    private array $validated = [];

    private array $htmlAllowedFields = [
        'content',
        'body',
        'description',
        'html_content',
        'post_content',
        'article_content',
        'bio',
        'about'
    ];

    private array $allowedHtmlTags = [
        'p',
        'br',
        'strong',
        'em',
        'b',
        'i',
        'u',
        'ul',
        'ol',
        'li',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'blockquote',
        'code',
        'pre',
        'a',
        'img',
        'div',
        'span',
        'table',
        'tr',
        'td',
        'th',
        'thead',
        'tbody',
        'tfoot'
    ];

    public function __construct(
        array $query = [],
        array $body = [],
        array $server = [],
        array $cookies = [],
        array $files = [],
        string $content = ''
    ) {
        $this->rawBodyParams = $body; // Store raw body params before sanitization
        $this->queryParams = $this->sanitizeArray($query, 'query');
        $this->bodyParams = $this->sanitizeArray($body, 'body');
        $this->serverParams = $server;
        $this->cookies = $this->sanitizeArray($cookies, 'query');
        $this->files = $this->processFiles($files);
        $this->content = $content;
        $this->method = $this->determineMethod();
        $this->path = $this->determinePath();
        $this->headers = $this->parseHeaders();
    }

    public static function createFromGlobals(): self
    {
        return new self(
            $_GET,
            self::parseRequestBody(),
            $_SERVER,
            $_COOKIE,
            $_FILES,
            file_get_contents('php://input') ?: ''
        );
    }

    private static function parseRequestBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $input = file_get_contents('php://input');

        if (str_starts_with($contentType, 'application/json')) {
            return json_decode($input, true) ?? [];
        }

        if (str_starts_with($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($input, $data);
            return $data;
        }

        return $_POST;
    }

    /**
     * Get the raw body content of the request
     *
     * @return string The raw request body
     */
    public function getBody(): string
    {
        return $this->content;
    }

    /**
     * Get the request body as JSON decoded array
     *
     * @return array|null The decoded JSON data or null if invalid
     */
    public function getJsonBody(): ?array
    {
        if (empty($this->content)) {
            return null;
        }

        $decoded = json_decode($this->content, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    /**
     * Get the request scheme (http or https)
     */
    public function getScheme(): string
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Get the request host
     */
    public function getHost(): string
    {
        return $this->serverParams['HTTP_HOST'] ?? 'localhost';
    }

    /**
     * Get the request port
     */
    public function getPort(): int
    {
        return (int) ($this->serverParams['SERVER_PORT'] ?? ($this->isSecure() ? 443 : 80));
    }

    /**
     * Get the base URL (scheme + host + port if non-standard)
     */
    public function getBaseUrl(): string
    {
        $scheme = $this->getScheme();
        $host = $this->getHost();
        $port = $this->getPort();

        // Only include port if it's non-standard
        $standardPorts = ['http' => 80, 'https' => 443];
        if ($port !== $standardPorts[$scheme]) {
            $host .= ':' . $port;
        }

        return "{$scheme}://{$host}";
    }

    /**
     * Get the request URI without query string
     */
    public function getPathInfo(): string
    {
        return $this->path;
    }

    /**
     * Get the query string
     */
    public function getQueryString(): ?string
    {
        return $this->serverParams['QUERY_STRING'] ?? null;
    }

    /**
     * Get the request URI (path + query string)
     */
    public function getRequestUri(): string
    {
        return $this->serverParams['REQUEST_URI'] ?? $this->path;
    }

    /**
     * Get user agent
     */
    public function getUserAgent(): string
    {
        return $this->serverParams['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Get referer
     */
    public function getReferer(): ?string
    {
        return $this->serverParams['HTTP_REFERER'] ?? null;
    }

    /**
     * Check if request matches a pattern
     */
    public function routeIs(string $pattern): bool
    {
        return preg_match('#^' . preg_quote($pattern, '#') . '$#', $this->path) === 1;
    }

    /**
     * Get all server parameters
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * Get a specific server parameter
     */
    public function getServerParam(string $key, $default = null)
    {
        return $this->serverParams[$key] ?? $default;
    }

    /**
     * Check if request is POST
     */
    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * Check if request is GET
     */
    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    /**
     * Check if request is PUT
     */
    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    /**
     * Check if request is DELETE
     */
    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    /**
     * Check if request is PATCH
     */
    public function isPatch(): bool
    {
        return $this->isMethod('PATCH');
    }

    /**
     * Get request data (alias for input)
     */
    public function post(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->bodyParams;
        }
        return $this->bodyParams[$key] ?? $default;
    }

    /**
     * Get request data (alias for input)
     */
    public function get(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->queryParams;
        }
        return $this->queryParams[$key] ?? $default;
    }

    public function validate(array $rules, bool $throw = true): bool
    {
        $validator = new Validator($this->all(), $rules);

        if ($validator->fails()) {
            $this->errors = $validator->errors();
            if ($throw) {
                throw new TreesException('Validation failed', 422, null, $this->errors);
            }
            return false;
        }

        // $this->validated = $validator->validated();
        return true;
    }

    public function validated(): array
    {
        if (empty($this->validated)) {
            throw new \LogicException('No validated data available. Call validate() first.');
        }
        return $this->validated;
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->bodyParams);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->bodyParams[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->queryParams[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->queryParams) || array_key_exists($key, $this->bodyParams);
    }

    public function only(array $keys): array
    {
        $all = $this->all();
        return array_intersect_key($all, array_flip($keys));
    }

    public function except(array $keys): array
    {
        $all = $this->all();
        return array_diff_key($all, array_flip($keys));
    }

    public function filled(string $key): bool
    {
        return $this->has($key) && !empty($this->input($key));
    }

    public function missing(string $key): bool
    {
        return !$this->has($key);
    }

    public function boolean(string $key, bool $default = false): bool
    {
        $value = $this->input($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function integer(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    public function float(string $key, float $default = 0.0): float
    {
        return (float) $this->input($key, $default);
    }

    public function string(string $key, string $default = ''): string
    {
        return (string) $this->input($key, $default);
    }

    /**
     * Get HTML content safely with basic sanitization
     */
    public function htmlContent(string $key, string $default = ''): string
    {
        $value = $this->rawInput($key, $default);

        if (empty($value)) {
            return $default;
        }

        // Basic security sanitization for HTML
        $allowedTags = '<' . implode('><', $this->allowedHtmlTags) . '>';
        $value = strip_tags((string)$value, $allowedTags);

        // Remove potentially dangerous attributes (more comprehensive)
        $value = preg_replace('/<([a-z][a-z0-9]*)[^>]*?(on\w+\s*=|javascript:|data:\s*text\/html|vbscript:|mocha:|livescript:)[^>]*?>/i', '<$1>', $value);

        // Remove style attributes that could contain dangerous CSS
        $value = preg_replace('/<([a-z][a-z0-9]*)[^>]*?\s+style\s*=[^>]*?>/i', '<$1>', $value);

        // Clean up any empty tags
        $value = preg_replace('/<([a-z]+)[^>]*?>\s*<\/\1>/i', '', $value);

        return trim($value);
    }

    /**
     * Get raw body parameter without sanitization
     */
    public function rawBody(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->rawBodyParams;
        }
        return $this->rawBodyParams[$key] ?? $default;
    }

    /**
     * Get specific input without sanitization
     */
    public function rawInput(string $key, mixed $default = null): mixed
    {
        return $this->rawBodyParams[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }

    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
    }

    public function isPrefetch(): bool
    {
        return strtolower($this->header('X-Purpose', '')) === 'prefetch' ||
            strtolower($this->header('X-Moz', '')) === 'prefetch';
    }

    public function url(): string
    {
        return $this->getBaseUrl() . $this->getPath();
    }

    public function fullUrl(): string
    {
        $url = $this->url();
        $query = http_build_query($this->queryParams);

        return $query ? "{$url}?{$query}" : $url;
    }

    public function is(string $pattern): bool
    {
        return preg_match('#^' . preg_quote($pattern, '#') . '$#', $this->path);
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key): UploadedFile|array|null
    {
        return $this->files[$key] ?? null;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isJson(): bool
    {
        return str_contains($this->header('Content-Type', ''), 'application/json');
    }

    public function wantsJson(): bool
    {
        return str_contains($this->header('Accept', ''), 'application/json');
    }

    public function prefersJson(): bool
    {
        $accept = $this->header('Accept', '');
        return str_contains($accept, 'application/json') ||
            str_contains($accept, 'application/*') ||
            str_contains($accept, '*/*');
    }

    public function isSecure(): bool
    {
        return ($this->serverParams['HTTPS'] ?? '') !== 'off'
            || ($this->serverParams['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }

    public function ip(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if ($ip = $this->serverParams[$key] ?? null) {
                return filter_var($ip, FILTER_VALIDATE_IP) ?: '';
            }
        }
        return '';
    }

    public function header(string $name, string $default = ''): string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');
        return preg_match('/Bearer\s+(\S+)/', $header, $matches) ? $matches[1] : null;
    }

    /**
     * Add fields that should allow HTML
     */
    public function allowHtmlFor(array $fields): void
    {
        $this->htmlAllowedFields = array_merge($this->htmlAllowedFields, $fields);
        $this->htmlAllowedFields = array_unique($this->htmlAllowedFields);
    }

    /**
     * Set allowed HTML tags for htmlContent method
     */
    public function setAllowedHtmlTags(array $tags): void
    {
        $this->allowedHtmlTags = $tags;
    }

    /**
     * Get current allowed HTML tags
     */
    public function getAllowedHtmlTags(): array
    {
        return $this->allowedHtmlTags;
    }

    /**
     * Get current HTML allowed fields
     */
    public function getHtmlAllowedFields(): array
    {
        return $this->htmlAllowedFields;
    }

    private function determineMethod(): string
    {
        $method = strtoupper($this->serverParams['REQUEST_METHOD'] ?? 'GET');

        if ($method === 'POST') {
            return strtoupper($this->serverParams['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? $method);
        }

        return $method;
    }

    private function determinePath(): string
    {
        $path = parse_url($this->serverParams['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return '/' . trim(rawurldecode($path), '/');
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($this->serverParams as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[strtolower($name)] = $value;
            }
        }
        return $headers;
    }

    private function sanitizeArray(array $data, string $type = 'body'): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $allowHtml = $type === 'body' && in_array($key, $this->htmlAllowedFields);
            $sanitized[$key] = $this->sanitize($value, $allowHtml);
        }
        return $sanitized;
    }

    private function sanitize(mixed $value, bool $allowHtml = false): mixed
    {
        if (is_array($value)) {
            return array_map(fn($item) => $this->sanitize($item, $allowHtml), $value);
        }

        if (is_numeric($value)) {
            return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }

        // Convert to string
        $value = (string)$value;

        // Allow HTML for specific fields
        if ($allowHtml) {
            // Basic sanitization for HTML content - remove potentially dangerous tags but keep allowed ones
            $allowedTags = '<' . implode('><', $this->allowedHtmlTags) . '>';
            $value = strip_tags($value, $allowedTags);

            // Remove dangerous attributes (more comprehensive)
            $value = preg_replace('/<([a-z][a-z0-9]*)[^>]*?(on\w+\s*=|javascript:|data:\s*text\/html|vbscript:|mocha:|livescript:)[^>]*?>/i', '<$1>', $value);

            // Remove style attributes that could contain dangerous CSS
            $value = preg_replace('/<([a-z][a-z0-9]*)[^>]*?\s+style\s*=[^>]*?>/i', '<$1>', $value);

            return $value;
        }

        // Default sanitization for regular text
        $value = strip_tags($value);
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function processFiles(array $files): array
    {
        $processed = [];

        foreach ($files as $key => $file) {
            // Handle multiple file uploads with same input name
            if (is_array($file['name'])) {
                foreach ($file['name'] as $index => $name) {
                    $processed[$key][$index] = new UploadedFile(
                        $file['tmp_name'][$index],
                        $name,
                        $file['type'][$index],
                        $file['error'][$index],
                        $file['size'][$index]
                    );
                }
            } else {
                $processed[$key] = new UploadedFile(
                    $file['tmp_name'],
                    $file['name'],
                    $file['type'],
                    $file['error'],
                    $file['size']
                );
            }
        }

        return $processed;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getDebugInfo(): array
    {
        return [
            'url' => $this->getPath(),
            'method' => $this->getMethod(),
            'query_params' => $this->queryParams,
            'body_params' => $this->bodyParams,
            'headers' => $this->headers,
            'cookies' => $this->cookies,
            'files' => array_map(function ($file) {
                if (is_array($file)) {
                    return array_map(fn($f) => $this->fileToArray($f), $file);
                }
                return $this->fileToArray($file);
            }, $this->files),
            'html_allowed_fields' => $this->htmlAllowedFields,
            'allowed_html_tags' => $this->allowedHtmlTags
        ];
    }

    private function fileToArray(UploadedFile $file): array
    {
        return [
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'type' => $file->getClientMimeType(),
            'error' => $file->getError()
        ];
    }
}
