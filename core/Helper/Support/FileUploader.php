<?php

declare(strict_types=1);

namespace Trees\Helper\Support;

use RuntimeException;
use Trees\Http\Request;
use Trees\Logger\Logger;
use InvalidArgumentException;

/**
 * =========================================
 * *****************************************
 * ======= Trees FileUploader Class ========
 * *****************************************
 * =========================================
 */

class FileUploader
{
    private string $uploadDir;
    private int $maxFileSize;
    private array $allowedMimeTypes;
    private bool $overwriteExisting;
    private ?Logger $logger;
    private ?Image $imageProcessor;
    private ?int $maxImageWidth;
    private ?int $maxImageHeight;
    private int $imageQuality = 85;
    private bool $optimizeImages = true;
    private int $qualityJpeg = 80;
    private int $qualityWebp = 80;
    private int $compressionPng = 6; // 0-9 where 9 is maximum compression
    private bool $convertToWebp = true; // Default to true for better compression
    private bool $deleteOriginalAfterConversion = true;
    private array $errorMessages = [
        'file_not_found' => 'File not found: %s',
        'file_not_writable' => 'File is not writable: %s',
        'upload_failed' => 'File upload failed',
        'invalid_type' => 'Invalid file type. Allowed types are: %s',
        'size_exceeded' => 'File exceeds maximum allowed size of %s bytes',
        'dangerous_type' => 'Uploading dangerous file types is prohibited',
        'directory_creation' => 'Failed to create upload directory: %s',
        'chunk_merge' => 'Failed to merge chunks',
        'image_operation' => 'Image operation failed: %s',
    ];

    public function __construct(
        string $uploadDir,
        int $maxFileSize = 10485760,
        array $allowedMimeTypes = ['image/jpeg', 'image/png'],
        bool $overwriteExisting = false,
        ?Logger $logger = null,
        ?Image $imageProcessor = null,
        ?int $maxImageWidth = null,
        ?int $maxImageHeight = null,
        ?int $imageQuality = null
    ) {
        $this->setUploadDir($uploadDir);
        $this->maxFileSize = $maxFileSize;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->overwriteExisting = $overwriteExisting;
        $this->logger = $logger ?? new Logger();
        $this->imageProcessor = $imageProcessor;
        $this->maxImageWidth = $maxImageWidth;
        $this->maxImageHeight = $maxImageHeight;

        if ($imageQuality !== null) {
            $this->setImageQuality($imageQuality);
        }
    }

    public function setUploadDir(string $directory): self
    {
        $directory = rtrim($directory, '/') . '/';

        if (!is_dir($directory)) {
            $this->ensureDirectoryExists($directory);
        }

        // Verify directory is writable (will throw exception if not)
        $this->ensureDirectoryWritable($directory);

        $this->uploadDir = $directory;
        return $this;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            // Create directory with proper permissions (755)
            if (!mkdir($directory, 0755, true)) {
                throw new RuntimeException(sprintf($this->errorMessages['directory_creation'], $directory));
            }

            // Set proper ownership (adjust www-data to your web server user)
            if (function_exists('posix_getuid')) {
                $webServerUser = posix_getuid();
                if (!chown($directory, $webServerUser)) {
                    $this->log("Failed to change owner for directory: {$directory}", 'warning');
                }
            }

            // Additional permission check
            if (!is_writable($directory)) {
                // Try to make it writable
                if (!chmod($directory, 0755)) {
                    throw new RuntimeException(sprintf($this->errorMessages['file_not_writable'], $directory));
                }
            }

            $this->log("Created upload directory: {$directory} with permissions 0755");
        }
    }

    /**
     * Verify directory is writable and attempt to fix if not
     */
    private function ensureDirectoryWritable(string $directory): void
    {
        if (!is_writable($directory)) {
            // Try to make it writable
            if (!chmod($directory, 0755)) {
                throw new RuntimeException(sprintf($this->errorMessages['file_not_writable'], $directory));
            }

            // Double check
            if (!is_writable($directory)) {
                throw new RuntimeException(sprintf($this->errorMessages['file_not_writable'], $directory));
            }
        }
    }

    /**
     * Get the web server user (if possible)
     */
    private function getWebServerUser(): ?string
    {
        if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
            $processUser = posix_getpwuid(posix_geteuid());
            return $processUser['name'] ?? null;
        }
        return null;
    }

    public function upload(UploadedFile $file, ?string $filename = null): string
    {
        $this->validateFile($file);

        $targetFilename = $filename ?? $this->generateUniqueFilename($file->getClientOriginalName());
        $targetPath = $this->uploadDir . $targetFilename;

        if (!$this->overwriteExisting && file_exists($targetPath)) {
            throw new RuntimeException(sprintf('File already exists: %s', $targetPath));
        }

        // Check if we need to process the image
        if ($this->shouldProcessImage($file)) {
            $tempPath = $this->processImageBeforeUpload($file);
            $movedPath = $this->moveUploadedFile($tempPath, $targetFilename);
        } else {
            $movedPath = $file->move($this->uploadDir, $targetFilename);
        }

        $this->log('File uploaded successfully: ' . $movedPath);

        return $movedPath;
    }

    public function uploadFromRequest(Request $request, string $fileKey, ?string $filename = null): ?string
    {
        $file = $request->file($fileKey);

        if ($file === null || !$file->isValid()) {
            return null;
        }

        return $this->upload($file, $filename);
    }

    public function uploadMultipleFromRequest(Request $request, string $fileKey): array
    {
        $files = $request->file($fileKey);

        if ($files === null) {
            throw new InvalidArgumentException(sprintf('No files uploaded with key: %s', $fileKey));
        }

        // Handle single file upload (which won't be an array)
        if (!is_array($files)) {
            return [$this->upload($files)];
        }

        $results = [];
        foreach ($files as $file) {
            try {
                $results[] = $this->upload($file);
            } catch (RuntimeException $e) {
                $this->log('Failed to upload file: ' . $e->getMessage(), 'error');
                $results[] = [
                    'error' => $e->getMessage(),
                    'file' => $file->getClientOriginalName()
                ];
            }
        }

        return $results;
    }

    private function shouldProcessImage(UploadedFile $file): bool
    {
        if ($this->imageProcessor === null) {
            return false;
        }

        $mimeType = mime_content_type($file->getPath());
        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true);
    }

    /**
     * Enable/disable image optimization
     */
    public function setOptimizeImages(bool $optimize): self
    {
        $this->optimizeImages = $optimize;
        return $this;
    }

    /**
     * Set quality settings for different image types
     */
    public function setQualitySettings(
        ?int $jpegQuality = null,
        ?int $webpQuality = null,
        ?int $pngCompression = null,
        ?bool $convertToWebp = null
    ): self {
        if ($jpegQuality !== null) $this->qualityJpeg = max(0, min(100, $jpegQuality));
        if ($webpQuality !== null) $this->qualityWebp = max(0, min(100, $webpQuality));
        if ($pngCompression !== null) $this->compressionPng = max(0, min(9, $pngCompression));
        if ($convertToWebp !== null) $this->convertToWebp = $convertToWebp;
        return $this;
    }

    private function processImageBeforeUpload(UploadedFile $file): string
    {
        $tempPath = $file->getPath() . '_processed';
        copy($file->getPath(), $tempPath);

        try {
            $this->imageProcessor->setQualitySettings(
                $this->qualityJpeg,
                $this->compressionPng,
                $this->qualityWebp
            );

            // First resize if needed
            if ($this->maxImageWidth !== null || $this->maxImageHeight !== null) {
                $tempPath = $this->resizeImage($tempPath);
            }

            // Convert to WebP if enabled
            if ($this->convertToWebp && $this->isConvertibleToWebp($tempPath)) {
                $tempPath = $this->imageProcessor->convertToWebp($tempPath);
                if ($this->deleteOriginalAfterConversion) {
                    @unlink($file->getPath()); // Delete original if converted to WebP
                }
            }

            // Apply additional optimizations
            return $this->imageProcessor->optimize($tempPath);
        } catch (\Exception $e) {
            $this->log('Image processing failed: ' . $e->getMessage(), 'error');
            unlink($tempPath);
            return $file->getPath();
        }
    }

    private function resizeImage(string $tempPath): string
    {
        if ($this->maxImageWidth !== null && $this->maxImageHeight !== null) {
            return $this->imageProcessor->resizeWithinDimensions(
                $tempPath,
                $this->maxImageWidth,
                $this->maxImageHeight,
                false
            );
        } elseif ($this->maxImageWidth !== null) {
            return $this->imageProcessor->resize($tempPath, $this->maxImageWidth, false);
        } else {
            return $this->imageProcessor->resizeToHeight($tempPath, $this->maxImageHeight, false);
        }
    }

    private function isConvertibleToWebp(string $path): bool
    {
        $mime = mime_content_type($path);
        return in_array($mime, ['image/jpg', 'image/jpeg', 'image/png', 'image/gif']);
    }

    private function moveUploadedFile(string $sourcePath, string $targetFilename): string
    {
        $targetPath = $this->uploadDir . $targetFilename;

        if (!rename($sourcePath, $targetPath)) {
            throw new RuntimeException('Failed to move processed file to destination');
        }

        // Set proper file permissions (644)
        if (!chmod($targetPath, 0644)) {
            $this->log("Failed to set permissions for file: {$targetPath}", 'warning');
        }

        return $targetPath;
    }

    public function setImageProcessor(Image $imageProcessor): self
    {
        $this->imageProcessor = $imageProcessor;
        return $this;
    }

    public function setMaxImageDimensions(?int $width, ?int $height): self
    {
        $this->maxImageWidth = $width;
        $this->maxImageHeight = $height;
        return $this;
    }

    public function setImageQuality(int $quality): self
    {
        $this->imageQuality = max(0, min(100, $quality));
        return $this;
    }

    public function setConvertToWebp(bool $convert, bool $deleteOriginal = true): self
    {
        $this->convertToWebp = $convert;
        $this->deleteOriginalAfterConversion = $deleteOriginal;
        return $this;
    }

    private function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new RuntimeException($this->getUploadErrorMessage($file->getError()));
        }

        if ($file->getSize() > $this->maxFileSize) {
            throw new RuntimeException(sprintf(
                $this->errorMessages['size_exceeded'],
                $this->maxFileSize
            ));
        }

        $detectedMimeType = mime_content_type($file->getPath());
        if (empty($detectedMimeType)) {
            throw new RuntimeException('Unable to determine file MIME type');
        }

        if (!in_array($detectedMimeType, $this->allowedMimeTypes, true)) {
            throw new RuntimeException(sprintf(
                $this->errorMessages['invalid_type'],
                implode(', ', $this->allowedMimeTypes)
            ));
        }

        $dangerousTypes = ['text/x-php', 'application/x-php', 'text/html', 'application/x-executable'];
        if (in_array($detectedMimeType, $dangerousTypes, true)) {
            throw new RuntimeException($this->errorMessages['dangerous_type']);
        }
    }

    private function generateUniqueFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    private function log(string $message, string $level = 'info'): void
    {
        // if ($this->logger !== null) {
        //     $this->logger->$level($message);
        // }
        try {
            $this->logger->$level($message);
        } catch (\Throwable $e) {
            // Fallback to error_log if logger fails
            error_log("[FileUploader] {$level}: {$message}");
        }
    }

    public function setMaxFileSize(int $size): self
    {
        $this->maxFileSize = $size;
        return $this;
    }

    public function setAllowedMimeTypes(array $types): self
    {
        $this->allowedMimeTypes = $types;
        return $this;
    }

    public function setOverwriteExisting(bool $overwrite): self
    {
        $this->overwriteExisting = $overwrite;
        return $this;
    }

    public function setLogger(Logger $logger): self
    {
        $this->logger = $logger;
        return $this;
    }
}
