<?php
declare(strict_types=1);

namespace Trees\Http;

use Trees\Exception\HttpException;

/**
 * =======================================
 * ***************************************
 * ========= Trees Response Class ========
 * ***************************************
 * =======================================
 */
class Response
{
    private array $headers = [];
    private array $cookies = [];
    private int $statusCode = 200;
    private $content = '';
    private bool $sent = false;

    public function __construct()
    {
        $this->setDefaultHeaders();
    }

    public function html(string $content): self
    {
        $this->content = $content;
        $this->setHeader('Content-Type', 'text/html; charset=UTF-8');
        return $this;
    }

    public function json($data, int $status = 200): self
    {
        $this->content = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $this->statusCode = $status;
        $this->setHeader('Content-Type', 'application/json; charset=UTF-8');
        return $this;
    }

    public function redirect(string $url, int $status = 302): self
    {
        $this->statusCode = $status;
        $this->setHeader('Location', $url);
        return $this;
    }

    /**
     * Output content directly without storing it
     */
    public function output(string $content): self
    {
        if ($this->sent) {
            throw new HttpException('Response already sent');
        }

        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        echo $content;
        $this->sent = true;
        return $this;
    }

    /**
     * Set multiple headers at once
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }
        return $this;
    }

    /**
     * Get a header value
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    /**
     * Check if a header exists
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * Remove a header
     */
    public function removeHeader(string $name): self
    {
        unset($this->headers[strtolower($name)]);
        return $this;
    }

    /**
     * Get all headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set a cookie
     */
    public function setCookie(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httpOnly' => $httpOnly,
            'sameSite' => $sameSite
        ];
        return $this;
    }

    /**
     * Delete a cookie
     */
    public function deleteCookie(string $name, string $path = '/', string $domain = ''): self
    {
        return $this->setCookie($name, '', time() - 3600, $path, $domain);
    }

    /**
     * Set cache headers
     */
    public function cache(int $seconds): self
    {
        $this->setHeader('Cache-Control', "max-age={$seconds}");
        $this->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $seconds) . ' GMT');
        return $this;
    }

    /**
     * Disable caching
     */
    public function noCache(): self
    {
        $this->setHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
        return $this;
    }

    /**
     * Set CORS headers
     */
    public function cors(
        array $origins = ['*'],
        array $methods = ['GET', 'POST', 'PUT', 'DELETE'],
        array $headers = ['Content-Type', 'Authorization']
    ): self {
        $this->setHeaders([
            'Access-Control-Allow-Origin' => implode(', ', $origins),
            'Access-Control-Allow-Methods' => implode(', ', $methods),
            'Access-Control-Allow-Headers' => implode(', ', $headers)
        ]);
        return $this;
    }

    /**
     * Send file download
     */
    public function download(string $filePath, string $filename = null, array $headers = []): self
    {
        if (!file_exists($filePath)) {
            throw new HttpException("File not found: {$filePath}", 404);
        }

        $filename = $filename ?: basename($filePath);
        $size = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        $this->setHeaders(array_merge([
            'Content-Type' => $mimeType,
            'Content-Length' => (string)$size,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public'
        ], $headers));

        $this->content = file_get_contents($filePath);
        return $this;
    }

    /**
     * Stream a file
     */
    public function stream(string $filePath, string $filename = null): self
    {
        if (!file_exists($filePath)) {
            throw new HttpException("File not found: {$filePath}", 404);
        }

        $filename = $filename ?: basename($filePath);
        $size = filesize($filePath);
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        if (!headers_sent()) {
            http_response_code($this->statusCode);
            
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . $size);
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header('Accept-Ranges: bytes');
            
            foreach ($this->headers as $name => $value) {
                if (!in_array(strtolower($name), ['content-type', 'content-length', 'content-disposition'])) {
                    header("{$name}: {$value}");
                }
            }
        }

        // Stream the file
        $handle = fopen($filePath, 'rb');
        if ($handle) {
            while (!feof($handle)) {
                echo fread($handle, 8192);
                flush();
            }
            fclose($handle);
        }

        $this->sent = true;
        return $this;
    }

    /**
     * Set XML content type
     */
    public function xml(string $content): self
    {
        $this->content = $content;
        $this->setHeader('Content-Type', 'application/xml; charset=UTF-8');
        return $this;
    }

    /**
     * Set plain text content type
     */
    public function text(string $content): self
    {
        $this->content = $content;
        $this->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        return $this;
    }

    /**
     * Set CSV content type
     */
    public function csv(string $content, string $filename = 'export.csv'): self
    {
        $this->content = $content;
        $this->setHeaders([
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
        return $this;
    }

    /**
     * Set status code with method chaining
     */
    public function setStatusCode(int $code): self
    {
        if ($code < 100 || $code > 599) {
            throw new HttpException("Invalid HTTP status code: {$code}", 500);
        }
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Get current status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set header with method chaining
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[strtolower($name)] = $value;
        return $this;
    }

    /**
     * Set content
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Check if response was sent
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * Send the response
     */
    public function send(): void
    {
        if ($this->sent) {
            throw new HttpException('Response already sent');
        }

        $this->sent = true;

        if (!headers_sent()) {
            http_response_code($this->statusCode);
            
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }

            foreach ($this->cookies as $cookie) {
                setcookie(
                    $cookie['name'],
                    $cookie['value'],
                    [
                        'expires' => $cookie['expires'],
                        'path' => $cookie['path'],
                        'domain' => $cookie['domain'],
                        'secure' => $cookie['secure'],
                        'httponly' => $cookie['httpOnly'],
                        'samesite' => $cookie['sameSite']
                    ]
                );
            }
        }

        echo $this->content;
    }

    /**
     * Set default security headers
     */
    private function setDefaultHeaders(): void
    {
        $this->headers = [
            'content-security-policy' => "default-src 'self'; " .
                                       "script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
                                       "style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'unsafe-inline'; " .
                                       "img-src 'self' data: https:; " .
                                       "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;",
            'x-content-type-options' => 'nosniff',
            'x-frame-options' => 'DENY',
            'x-xss-protection' => '1; mode=block',
            'referrer-policy' => 'strict-origin-when-cross-origin'
        ];

        // Only add HSTS in production/HTTPS
        if ($this->isHttps()) {
            $this->headers['strict-transport-security'] = 'max-age=31536000; includeSubDomains';
        }
    }

    /**
     * Check if connection is HTTPS
     */
    private function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
               (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    }

    public function __destruct()
    {
        if (!$this->sent) {
            $this->send();
        }
    }
}