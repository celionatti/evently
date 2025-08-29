<?php

declare(strict_types=1);

namespace Trees\View;

use Trees\Exception\TreesException;
use ReflectionFunction;
use Closure;

/**
 * =======================================
 * Trees View Engine
 * =======================================
 *
 * A secure, efficient template engine with:
 * - Template inheritance
 * - Section management
 * - Compiled templates with caching
 * - Context-aware escaping
 * - CSRF protection
 */
class View
{
    // Configuration constants
    private const TEMPLATE_EXTENSION = '.php';
    private const CACHE_FILE_PREFIX = 'tpl_';
    private const CACHE_TTL = 3600; // 1 hour
    private const TEMP_FILE_PREFIX = 'temp_';

    // Directives
    private array $directives = [];
    private array $templateDependencies = [];
    private bool $cacheDirty = false;

    // Properties
    private string $title = 'Trees PHP Framework | Welcome';
    private string $header = 'Dashboard';
    private array $sections = [];
    private array $sectionStack = [];
    private ?string $currentSection = null;
    private string $layout = 'default';
    private string $baseTemplatePath;
    private string $baseCachePath;
    private bool $debug = true;
    private bool $cacheEnabled = true;

    public function __construct(?string $templatesPath = null, ?string $cachePath = null)
    {
        $this->baseTemplatePath = $templatesPath ?? ROOT_PATH . '/resources';
        $this->baseCachePath = $cachePath ?? ROOT_PATH . '/storage/cache';
        
        // Ensure cache directory exists
        $this->ensureDirectoryExists($this->baseCachePath);
        
        $this->registerDirectives();
    }

    private function registerDirectives(): void
    {
        $this->directives = [
            // Control structures
            '@if' => fn($expr) => '<?php if(' . $expr . '): ?>',
            '@else' => fn() => '<?php else: ?>',
            '@elseif' => fn($expr) => '<?php elseif(' . $expr . '): ?>',
            '@endif' => fn() => '<?php endif; ?>',

            // Loops
            '@foreach' => fn($expr) => '<?php foreach(' . $expr . '): ?>',
            '@endforeach' => fn() => '<?php endforeach; ?>',
            '@for' => fn($expr) => '<?php for(' . $expr . '): ?>',
            '@endfor' => fn() => '<?php endfor; ?>',
            '@while' => fn($expr) => '<?php while(' . $expr . '): ?>',
            '@endwhile' => fn() => '<?php endwhile; ?>',

            // Switch statements
            '@switch' => fn($expr) => '<?php switch(' . $expr . '): ?>',
            '@case' => fn($expr) => '<?php case ' . $expr . ': ?>',
            '@break' => fn() => '<?php break; ?>',
            '@default' => fn() => '<?php default: ?>',
            '@endswitch' => fn() => '<?php endswitch; ?>',

            // Template inheritance
            '@extends' => fn($expr) => '<?php $this->setLayout(' . $expr . '); ?>',
            '@section' => fn($expr) => '<?php $this->start(' . $expr . '); ?>',
            '@endsection' => fn() => '<?php $this->end(); ?>',
            '@yield' => fn($expr) => '<?php $this->content(' . $expr . '); ?>',
            '@parent' => fn() => '<?php echo $this->getParentSection(); ?>',

            // Authentication
            '@auth' => fn() => '<?php if(function_exists("auth") && auth()->check()): ?>',
            '@endauth' => fn() => '<?php endif; ?>',
            '@guest' => fn() => '<?php if(function_exists("auth") && auth()->guest()): ?>',
            '@endguest' => fn() => '<?php endif; ?>',

            // Conditionals
            '@isset' => fn($expr) => '<?php if(isset(' . $expr . ')): ?>',
            '@endisset' => fn() => '<?php endif; ?>',
            '@empty' => fn($expr) => '<?php if(empty(' . $expr . ')): ?>',
            '@endempty' => fn() => '<?php endif; ?>',

            // CSRF protection
            '@csrf' => fn() => '<?php if(function_exists("csrf_token")): ?>' .
                '<input type="hidden" name="csrf_token" value="<?= $this->escape(csrf_token()); ?>">' .
                '<?php endif; ?>',

            // Includes
            '@include' => fn($expr) => '<?php $this->partial(' . $expr . '); ?>',
            '@component' => fn($expr) => '<?php $this->component(' . $expr . '); ?>',

            // Escaping
            '@escape' => fn($expr) => '<?php echo $this->escape(' . $expr . '); ?>',
            '@raw' => fn($expr) => '<?php echo ' . $expr . '; ?>',
        ];
    }

    // Configuration methods
    public function setTemplatePath(string $path): self
    {
        $this->baseTemplatePath = rtrim($path, '/\\');
        return $this;
    }

    public function setCachePath(string $path): self
    {
        $this->baseCachePath = rtrim($path, '/\\');
        $this->ensureDirectoryExists($this->baseCachePath);
        return $this;
    }

    public function enableCache(bool $enabled = true): self
    {
        $this->cacheEnabled = $enabled;
        return $this;
    }

    public function enableDebug(bool $enabled = true): self
    {
        $this->debug = $enabled;
        return $this;
    }

    // Template rendering
    public function render(string $path, array $params = [], bool $returnOutput = false): ?string
    {
        try {
            $output = $this->renderTemplate($path, $params);

            if ($returnOutput) {
                return $output;
            }

            echo $output;
            return null;
        } catch (TreesException $e) {
            if ($returnOutput) {
                throw $e;
            }

            $this->handleError($e);
            return null;
        }
    }

    public function renderJson(array $data, int $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP): void
    {
        header('Content-Type: application/json');
        echo json_encode($data, $options);
    }

    // Section management
    public function start(string $key): void
    {
        if (empty($key)) {
            throw new TreesException("Section key cannot be empty");
        }

        if ($this->currentSection !== null) {
            $this->end();
        }

        $this->currentSection = $key;
        $this->sectionStack[] = $key;
        ob_start();
    }

    public function end(): void
    {
        if (empty($this->sectionStack)) {
            return;
        }

        $key = array_pop($this->sectionStack);
        $this->sections[$key] = ob_get_clean();
        $this->currentSection = end($this->sectionStack) ?: null;
    }

    public function content(string $key): void
    {
        echo $this->sections[$key] ?? '';
    }

    public function getParentSection(): string
    {
        $parent = $this->sectionStack[count($this->sectionStack) - 2] ?? null;
        return $parent ? ($this->sections[$parent] ?? '') : '';
    }

    // Template components
    public function partial(string $path, array $params = []): void
    {
        $partialPath = $this->resolvePath('partials/' . $path);
        if (file_exists($partialPath)) {
            // Create a closure to isolate the scope
            (function () use ($params, $partialPath) {
                extract($params, EXTR_SKIP);
                include $partialPath;
            })();
        } else {
            throw new TreesException("Partial view not found: $partialPath", 404);
        }
    }

    public function component(string $name, array $data = [], array $slots = []): void
    {
        $this->renderComponent('components' . DIRECTORY_SEPARATOR . $name, $data, $slots);
    }

    // Layout management
    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setHeader(string $header): self
    {
        $this->header = $header;
        return $this;
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    protected function hasContentChanged(string $cachePath, string $newContent): bool
    {
        if (!file_exists($cachePath)) {
            return true;
        }

        $existingContent = file_get_contents($cachePath);
        return $existingContent !== $newContent;
    }

    // Cache management
    public function clearCache(): bool
    {
        $files = glob($this->baseCachePath . '/' . self::CACHE_FILE_PREFIX . '*');
        $success = true;

        foreach ($files as $file) {
            if (is_file($file)) {
                $success = $success && unlink($file);
            }
        }

        return $success;
    }

    // Protected methods
    protected function renderTemplate(string $path, array $params = []): string
    {
        $viewPath = $this->resolvePath($path);
        $layoutPath = $this->resolvePath('layouts' . DIRECTORY_SEPARATOR . $this->layout);

        if (!file_exists($viewPath)) {
            throw new TreesException("View not found: $path", 404);
        }

        // Extract parameters into variables
        extract($params, EXTR_SKIP);

        // Render the view content
        $viewContent = $this->getCompiledTemplate($viewPath);
        $viewOutput = $this->evaluateTemplate($viewContent, $params, $viewPath);

        // Render the layout with sections
        $layoutContent = $this->getCompiledTemplate($layoutPath);
        $output = $this->evaluateTemplate($layoutContent, $params, $layoutPath);

        return $output;
    }

    protected function renderComponent(string $path, array $data = [], array $slots = []): void
    {
        $componentPath = $this->resolvePath($path);

        if (!file_exists($componentPath)) {
            throw new TreesException("Component not found: $path", 404);
        }

        $originalSections = $this->sections;
        $this->sections = $slots;

        $content = $this->getCompiledTemplate($componentPath);
        $this->evaluateTemplate($content, $data, $componentPath);

        $this->sections = $originalSections;
    }

    protected function getCompiledTemplate(string $templatePath): string
    {
        if (!$this->cacheEnabled) {
            return $this->compileTemplate(file_get_contents($templatePath));
        }

        $cachePath = $this->getCachePath($templatePath);

        // Check if cache is valid
        if ($this->isCacheValid($templatePath, $cachePath)) {
            // Store dependency information
            $this->templateDependencies[$cachePath] = [
                'template' => $templatePath,
                'mtime' => filemtime($templatePath)
            ];
            return file_get_contents($cachePath);
        }

        $compiled = $this->compileTemplate(file_get_contents($templatePath));

        // Only update cache if content has changed
        if (!$this->hasContentChanged($cachePath, $compiled)) {
            // Update modification time to extend cache validity
            touch($cachePath);
            $this->templateDependencies[$cachePath] = [
                'template' => $templatePath,
                'mtime' => filemtime($templatePath)
            ];
            return $compiled;
        }

        $this->storeCache($cachePath, $compiled);
        $this->cacheDirty = true;
        $this->templateDependencies[$cachePath] = [
            'template' => $templatePath,
            'mtime' => filemtime($templatePath)
        ];

        return $compiled;
    }

    // Add a method to check multiple templates at once
    public function isCacheFresh(array $templates): bool
    {
        if (!$this->cacheEnabled) {
            return false;
        }

        foreach ($templates as $template) {
            $templatePath = $this->resolvePath($template);
            $cachePath = $this->getCachePath($templatePath);

            if (!$this->isCacheValid($templatePath, $cachePath)) {
                return false;
            }
        }

        return true;
    }

    // Add a method to clear stale cache files
    public function clearStaleCache(): int
    {
        $files = glob($this->baseCachePath . '/' . self::CACHE_FILE_PREFIX . '*');
        $cleared = 0;
        $now = time();

        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > self::CACHE_TTL) {
                if (unlink($file)) {
                    $cleared++;
                    unset($this->templateDependencies[$file]);
                }
            }
        }

        return $cleared;
    }

    protected function compileTemplate(string $template): string
    {
        // Process directives
        foreach ($this->directives as $pattern => $callback) {
            $template = $this->compileDirectives($template, $pattern, $callback);
        }

        // Process {{{ }}} expressions (raw) - changed from {!! !!}
        $template = preg_replace_callback('/\{\{\{\s*(.+?)\s*\}\}\}/', function ($matches) {
            return '<?php echo ' . $matches[1] . '; ?>';
        }, $template);

        // Process {{ }} expressions (escaped)
        $template = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function ($matches) {
            return '<?php echo $this->escape(' . $matches[1] . '); ?>';
        }, $template);

        return $template;
    }

    protected function compileDirectives(string $template, string $pattern, callable $callback): string
    {
        $ref = new ReflectionFunction(Closure::fromCallable($callback));
        $requiredParams = $ref->getNumberOfRequiredParameters();

        $escapedPattern = preg_quote($pattern, '/');

        if ($requiredParams > 0) {
            return preg_replace_callback(
                '/' . $escapedPattern . '\s*\(([^()]*(?:\([^()]*\)[^()]*)*)\)/',
                fn($matches) => $callback(trim($matches[1])),
                $template
            );
        }

        return preg_replace_callback(
            '/' . $escapedPattern . '(?![a-zA-Z0-9_])/',
            fn() => $callback(),
            $template
        );
    }

    // Security and utility methods
    protected function escape($value): string
    {
        if (is_array($value) || is_object($value)) {
            return htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8');
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    protected function resolvePath(string $path, string $type = 'resources'): string
    {
        // Normalize path separators and remove parent directory references
        $path = str_replace(['..', '\\'], ['', '/'], $path);
        $path = ltrim($path, '/');

        // Ensure the path ends with the correct extension
        if (!str_ends_with($path, self::TEMPLATE_EXTENSION)) {
            $path .= self::TEMPLATE_EXTENSION;
        }

        $basePath = $type === 'resources' ? $this->baseTemplatePath : $this->baseCachePath;

        // Combine paths safely
        $fullPath = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR . ltrim($path, '/\\');

        // For cache files, don't check existence (we may be creating them)
        if ($type === 'resources' && !file_exists($fullPath)) {
            throw new TreesException("Template file not found: $path");
        }

        // Security check - verify the resolved path is within base directory
        $normalizedBase = realpath($basePath);
        
        // If the base path doesn't exist, create it for cache directories
        if ($normalizedBase === false && $type === 'cache') {
            $this->ensureDirectoryExists($basePath);
            $normalizedBase = realpath($basePath);
        }
        
        $normalizedFull = realpath(dirname($fullPath)) ?: $fullPath;

        if ($normalizedBase === false || strpos($normalizedFull, $normalizedBase) !== 0) {
            throw new TreesException("Invalid path: attempted directory traversal");
        }

        return $fullPath;
    }

    protected function getCachePath(string $templatePath): string
    {
        $hash = md5($templatePath);
        return $this->resolvePath(self::CACHE_FILE_PREFIX . $hash, 'cache');
    }

    protected function getTempPath(): string
    {
        $tempName = uniqid(self::TEMP_FILE_PREFIX, true);
        return $this->resolvePath($tempName, 'cache');
    }

    protected function isCacheValid(string $templatePath, string $cachePath): bool
    {
        return file_exists($cachePath) &&
            filemtime($cachePath) > filemtime($templatePath) &&
            (time() - filemtime($cachePath)) < self::CACHE_TTL;
    }

    protected function storeCache(string $cachePath, string $content): void
    {
        if (!$this->hasContentChanged($cachePath, $content)) {
            return;
        }

        $this->ensureDirectoryExists(dirname($cachePath));

        // Atomic write to prevent race conditions
        $tempFile = tempnam($this->baseCachePath, self::TEMP_FILE_PREFIX);

        if (file_put_contents($tempFile, $content, LOCK_EX) !== false) {
            if (rename($tempFile, $cachePath)) {
                return;
            }
            unlink($tempFile);
        }

        throw new TreesException("Failed to write cache file: $cachePath");
    }

    protected function evaluateTemplate(string $compiledContent, array $data, string $templatePath): string
    {
        extract($data, EXTR_SKIP);

        // Create a temporary file in the cache directory
        $tempFile = $this->getTempPath();

        try {
            // Write the compiled content to the temp file
            if (file_put_contents($tempFile, $compiledContent, LOCK_EX) === false) {
                throw new TreesException("Failed to write temporary template file");
            }

            // Clean any existing output buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Start output buffering
            if (!ob_start()) {
                throw new TreesException("Failed to start output buffering");
            }

            try {
                // Include the temp file
                include $tempFile;

                // Get the output and clean the buffer
                $output = ob_get_clean();

                // Delete the temp file
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }

                return $output;
            } catch (\Throwable $e) {
                // Ensure buffer is cleaned and temp file is deleted
                ob_end_clean();
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
                throw $e;
            }
        } catch (\Throwable $e) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw new TreesException(
                sprintf("Error rendering template %s: %s", basename($templatePath), $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    protected function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new TreesException("Failed to create directory: $path");
        }
    }

    protected function handleError(TreesException $e): void
    {
        if ($this->debug) {
            // Enhanced debug output
            echo '<div style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; font-family: monospace;">';
            echo '<h2 style="margin-top: 0;">Template Error</h2>';
            echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';

            if ($e->getPrevious()) {
                echo '<p><strong>Previous Exception:</strong> ' . htmlspecialchars($e->getPrevious()->getMessage()) . '</p>';
            }

            echo '<h3>Stack Trace:</h3>';
            echo '<pre style="background: #fff; padding: 10px; border-radius: 3px; overflow: auto;">';
            echo htmlspecialchars($e->getTraceAsString());
            echo '</pre>';

            // Show template context if available
            if (isset($templatePath)) {
                echo '<h3>Template:</h3>';
                echo '<pre style="background: #fff; padding: 10px; border-radius: 3px; overflow: auto;">';
                $lines = file($templatePath);
                $start = max(0, $e->getLine() - 5);
                $end = min(count($lines), $e->getLine() + 5);

                for ($i = $start; $i < $end; $i++) {
                    $lineNum = $i + 1;
                    $highlight = ($lineNum === $e->getLine()) ? 'background: #ffeb3b;' : '';
                    echo "<span style='{$highlight}'>" . sprintf("%4d", $lineNum) . " | " . htmlspecialchars($lines[$i]) . "</span>";
                }
                echo '</pre>';
            }

            echo '</div>';
        } else {
            // Your existing production error handling
            try {
                $statusCode = $e->getCode() ?: 500;
                $errorTemplate = $this->resolvePath('errors/' . $statusCode);
                include $errorTemplate;
            } catch (TreesException $templateError) {
                echo '<h1>An error occurred</h1>';
                echo '<p>Sorry, something went wrong.</p>';

                if ($e->getCode() === 404) {
                    echo '<p>The requested page could not be found.</p>';
                }
            }
        }
    }

    protected function cleanupOldTempFiles(): void
    {
        $files = glob($this->baseCachePath . '/' . self::TEMP_FILE_PREFIX . '*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > 3600) {
                @unlink($file);
            }
        }
    }
}