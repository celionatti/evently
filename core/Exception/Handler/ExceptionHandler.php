<?php

declare(strict_types=1);

namespace Trees\Exception\Handler;

use PDOException;
use Throwable;
use Trees\Http\Request;
use Trees\Exception\TreesException;

class ExceptionHandler
{
    private string $environment;
    private bool $debug;
    private string $logPath;
    private array $dontReport = [];

    public function __construct(string $environment = null)
    {
        $this->environment = $environment ?? ($_ENV['APP_ENV'] ?? 'production');
        $this->debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true' || $this->environment === 'development';
        $this->logPath = $this->getLogPath();
        $this->configureErrorHandling();
    }

    private function configureErrorHandling(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', $this->debug ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', $this->logPath);

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function dontReport(array $exceptions): void
    {
        $this->dontReport = $exceptions;
    }

    private function getLogPath(): string
    {
        $logDir = defined('STORAGE_PATH')
            ? STORAGE_PATH . '/logs'
            : (defined('ROOT_PATH')
                ? ROOT_PATH . '/storage/logs'
                : dirname(__DIR__, 3) . '/storage/logs');

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        return $logDir . '/error-' . date('Y-m-d') . '.log';
    }

    public function handleError(int $level, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }

        $this->handleException(new \ErrorException($message, 0, $level, $file, $line));
        return true;
    }

    public function handleException(Throwable $exception): void
    {
        if ($this->shouldNotReport($exception)) {
            return;
        }

        $this->logException($exception);
        $this->clearOutputBuffers();

        if ($this->debug) {
            $this->renderDevelopmentError($exception);
        } else {
            $this->renderProductionError($exception);
        }

        exit(1);
    }

    public function handleHttpException(Throwable $exception, Request $request): void
    {
        if ($this->shouldNotReport($exception)) {
            return;
        }

        $this->logException($exception);
        $this->clearOutputBuffers();

        if ($this->debug) {
            $this->renderDevelopmentError($exception, $this->getRequestData($request));
        } else {
            $this->renderProductionError($exception);
        }

        exit(1);
    }

    private function shouldNotReport(Throwable $exception): bool
    {
        foreach ($this->dontReport as $type) {
            if ($exception instanceof $type) {
                return true;
            }
        }
        return false;
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->clearOutputBuffers();
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    private function logException(Throwable $exception): void
    {
        $logEntry = sprintf(
            "[%s] %s: %s in %s on line %d\nStack trace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        error_log($logEntry, 3, $this->logPath);
    }

    private function clearOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    private function renderDevelopmentError(Throwable $exception, array $requestData = []): void
    {
        http_response_code($this->getStatusCode($exception));
        header('Content-Type: text/html; charset=utf-8');

        $title = $this->getErrorTitle($exception);
        $requestData = empty($requestData) ? $this->getRequestData() : $requestData;
        $environment = $this->getEnvironmentInfo();
        $solutions = $this->getSuggestedSolutions($exception);

        $this->renderErrorView('development', compact(
            'exception', 'title', 'requestData', 'environment', 'solutions'
        ));
    }

    private function renderProductionError(Throwable $exception): void
    {
        http_response_code($this->getStatusCode($exception));
        header('Content-Type: text/html; charset=utf-8');

        $errorId = uniqid('err-', true);
        $this->logException($exception);

        $this->renderErrorView('production', [
            'errorId' => $errorId,
            'statusCode' => $this->getStatusCode($exception),
            'statusText' => $this->getStatusText($this->getStatusCode($exception))
        ]);
    }

    private function renderErrorView(string $type, array $data): void
    {
        extract($data);

        if ($type === 'development') {
            include ROOT_PATH . '/resources/errors/development.php';
        } else {
            include ROOT_PATH . '/resources/errors/production.php';
        }
    }

    private function getStatusCode(Throwable $exception): int
    {
        if ($exception instanceof TreesException && $exception->getCode() >= 400 && $exception->getCode() < 600) {
            return $exception->getCode();
        }

        if ($exception instanceof PDOException) {
            return 503; // Service Unavailable
        }

        return 500;
    }

    private function getStatusText(int $statusCode): string
    {
        $statusTexts = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
        ];

        return $statusTexts[$statusCode] ?? 'Internal Server Error';
    }

    private function getErrorTitle(Throwable $exception): string
    {
        $statusCode = $this->getStatusCode($exception);
        $statusText = $this->getStatusText($statusCode);

        return sprintf('%d %s', $statusCode, $statusText);
    }

    private function getRequestData(?Request $request = null): array
    {
        if ($request && method_exists($request, 'getDebugInfo')) {
            return $request->getDebugInfo();
        }

        return [
            'server' => $_SERVER,
            'get' => $_GET,
            'post' => $_POST,
            'cookies' => $_COOKIE,
            'session' => $_SESSION ?? [],
            'files' => $_FILES,
        ];
    }

    private function getEnvironmentInfo(): array
    {
        return [
            'php_version' => phpversion(),
            'environment' => $this->environment,
            'debug_mode' => $this->debug,
            'os' => PHP_OS,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
            'timezone' => date_default_timezone_get(),
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'peak_memory_usage' => $this->formatBytes(memory_get_peak_usage(true)),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function getSuggestedSolutions(Throwable $exception): array
    {
        $solutions = [];

        if ($exception instanceof PDOException) {
            $solutions[] = "Check your database connection settings.";
            $solutions[] = "Verify database credentials in your configuration.";
            $solutions[] = "Ensure the database server is running and accessible.";
        }
        elseif ($exception instanceof \ErrorException) {
            $solutions[] = "Check for undefined variables or syntax errors.";
            $solutions[] = "Verify all required files exist and are readable.";
        }
        elseif ($exception instanceof \TypeError) {
            $solutions[] = "Check your type hints and ensure correct types are being passed.";
        }
        elseif ($exception instanceof \RuntimeException) {
            $solutions[] = "Check file permissions and resource availability.";
        }

        return $solutions;
    }
}

function treesErrorHandler(): ExceptionHandler
{
    static $handler = null;

    if ($handler === null) {
        $handler = new ExceptionHandler($_ENV['APP_ENV'] ?? 'production');

        // Add exceptions that shouldn't be logged
        $handler->dontReport([
            \Trees\Exception\NotFoundException::class,
            \Trees\Exception\UnauthorizedException::class,
        ]);
    }

    return $handler;
}

// Initialize the handler
treesErrorHandler();