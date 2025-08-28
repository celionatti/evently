<?php

declare(strict_types=1);

namespace Trees\Helper\Support;

use Trees\Trees;
use Trees\Exception\TreesException;

/**
 * =========================================
 * *****************************************
 * ======== Trees ExtensionCheck Class =====
 * *****************************************
 * =========================================
 */

class ExtensionCheck
{
    private array $requiredExtensions = [];
    private array $defaultExtensions = [
        'gd',
        'mysqli',
        'pdo_mysql',
        'pdo_sqlite',
        'curl',
        'fileinfo',
        'intl',
        'exif',
        'mbstring',
    ];

    public const ERROR_HANDLER_EXCEPTION = 'exception';
    public const ERROR_HANDLER_DIE = 'die';

    private string $errorHandler = self::ERROR_HANDLER_EXCEPTION;

    /**
     * Add a required PHP extension
     */
    public function addRequiredExtension(string $extension): self
    {
        if (!in_array($extension, $this->requiredExtensions, true)) {
            $this->requiredExtensions[] = $extension;
        }
        return $this;
    }

    /**
     * Remove a required PHP extension
     */
    public function removeRequiredExtension(string $extension): self
    {
        $this->requiredExtensions = array_filter(
            $this->requiredExtensions,
            fn($ext) => $ext !== $extension
        );
        return $this;
    }

    /**
     * Set the error handling method
     */
    public function setErrorHandler(string $handler): self
    {
        if (!in_array($handler, [self::ERROR_HANDLER_EXCEPTION, self::ERROR_HANDLER_DIE], true)) {
            throw new TreesException('Invalid error handler specified', 500);
        }
        $this->errorHandler = $handler;
        return $this;
    }

    /**
     * Check if all required extensions are loaded
     */
    public function checkExtensions(): bool
    {
        $missingExtensions = $this->getMissingExtensions();

        if (!empty($missingExtensions)) {
            $this->handleMissingExtensions($missingExtensions);
            return false;
        }

        return true;
    }

    /**
     * Get list of missing extensions
     */
    public function getMissingExtensions(): array
    {
        $allRequired = array_unique(array_merge(
            $this->defaultExtensions,
            $this->requiredExtensions
        ));

        return array_filter($allRequired, fn($ext) => !extension_loaded($ext));
    }

    /**
     * Handle missing extensions based on configured error handler
     */
    private function handleMissingExtensions(array $missingExtensions): void
    {
        $message = $this->buildErrorMessage($missingExtensions);

        match ($this->errorHandler) {
            self::ERROR_HANDLER_EXCEPTION => throw new TreesException(
                $message,
                500,
            ),
            self::ERROR_HANDLER_DIE => dd($message, 'Missing Extensions'),
            default => throw new TreesException(
                'Invalid error handler configured',
                500,
            ),
        };
    }

    /**
     * Build a formatted error message for missing extensions
     */
    private function buildErrorMessage(array $missingExtensions): string
    {
        $extensionList = array_map(
            fn($ext) => "- $ext",
            $missingExtensions
        );

        return sprintf(
            "The following PHP extensions are required but not loaded:\n%s\n" .
            "Please enable them in your php.ini configuration.",
            implode("\n", $extensionList)
        );
    }

    /**
     * Set custom default extensions (overrides built-in defaults)
     */
    public function setDefaultExtensions(array $extensions): self
    {
        $this->defaultExtensions = $extensions;
        return $this;
    }

    /**
     * Add multiple required extensions at once
     */
    public function addRequiredExtensions(array $extensions){}
}