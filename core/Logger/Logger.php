<?php

declare(strict_types=1);

namespace Trees\Logger;

use Trees\Exception\TreesException;

/**
 * =======================================
 * ***************************************
 * ========= Trees Logger Class ==========
 * ***************************************
 * =======================================
 */

class Logger
{
    /**
     * @var string Log file path
     */
    private static string $logFile = 'storage/logs/database.log';

    /**
     * @var int Log level (0 = none, 1 = errors, 2 = warnings, 3 = info)
     */
    private static int $logLevel = 3;

    /**
     * Set the log file path
     *
     * @param string $path The log file path
     * @return void
     */
    public static function setLogFile(string $path): void
    {
        self::$logFile = $path;
    }

    /**
     * Set the log level
     *
     * @param int $level The log level
     * @return void
     */
    public static function setLogLevel(int $level): void
    {
        self::$logLevel = $level;
    }

    /**
     * Log an error message
     *
     * @param string $message The error message
     * @param array $context Additional context data
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        if (self::$logLevel >= 1) {
            self::log('ERROR', $message, $context);
        }
    }

    /**
     * Log a warning message
     *
     * @param string $message The warning message
     * @param array $context Additional context data
     * @return void
     */
    public static function warning(string $message, array $context = []): void
    {
        if (self::$logLevel >= 2) {
            self::log('WARNING', $message, $context);
        }
    }

    /**
     * Log an info message
     *
     * @param string $message The info message
     * @param array $context Additional context data
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        if (self::$logLevel >= 3) {
            self::log('INFO', $message, $context);
        }
    }

    /**
     * Log a message
     *
     * @param string $level The log level
     * @param string $message The log message
     * @param array $context Additional context data
     * @return void
     */
    private static function log(string $level, string $message, array $context = []): void
    {
        $logFilePath = ROOT_PATH . DIRECTORY_SEPARATOR . self::$logFile;
        $logDir = dirname($logFilePath);

        // Create log directory if it doesn't exist
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true) && !is_dir($logDir)) {
                return;
            }
        }

        // Ensure the directory is writable
        if (!is_writable($logDir)) {
            return;
        }

        // Format the log message
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        $logMessage = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;

        // Write to log file
        file_put_contents($logFilePath, $logMessage, FILE_APPEND);
    }

    /**
     * Log an exception
     *
     * @param \Throwable $e The exception to log
     * @return void
     */
    public static function exception(\Throwable $e): void
    {
        $context = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];

        if ($e instanceof TreesException) {
            $context['query'] = $e->getMessage();
            $context['params'] = $e->getErrors();
            $context['error_code'] = $e->getCode();
        }

        self::error($e->getMessage(), $context);
    }
}