<?php

declare(strict_types=1);

namespace Trees\Helper\Support;

use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * =========================================
 * *****************************************
 * ======== Trees VideoUpload Class ========
 * *****************************************
 * =========================================
 */

class VideoUpload
{
    protected string $uploadDir;
    protected int $maxFileSize;
    protected array $allowedMimeTypes;
    protected array $errorMessages = [];
    protected bool $overwriteExisting = false;
    protected array $ffmpegConfig = [];

    // Default allowed video MIME types
    protected const DEFAULT_ALLOWED_TYPES = [
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/x-ms-wmv',
        'video/webm',
        'video/ogg'
    ];

    // Default error messages
    protected const DEFAULT_ERROR_MESSAGES = [
        'file_not_found' => 'File not found: %s',
        'upload_failed' => 'File upload failed',
        'invalid_type' => 'Invalid file type. Allowed types are: %s',
        'size_exceeded' => 'File exceeds maximum allowed size of %s bytes',
        'directory_creation' => 'Failed to create upload directory: %s',
        'file_exists' => 'File already exists: %s',
        'file_not_writable' => 'File is not writable: %s',
        'ffmpeg_missing' => 'FFmpeg is not installed or not configured properly',
        'thumbnail_failed' => 'Failed to generate thumbnail',
        'invalid_duration' => 'Video duration exceeds maximum allowed duration of %s seconds',
        'invalid_dimensions' => 'Video dimensions exceed maximum allowed dimensions of %sx%s'
    ];

    public function __construct(
        string $uploadDir,
        int $maxFileSize = 104857600, // 100MB default
        array $allowedMimeTypes = null,
        array $ffmpegConfig = []
    ) {
        $this->setUploadDir($uploadDir);
        $this->maxFileSize = $maxFileSize;
        $this->allowedMimeTypes = $allowedMimeTypes ?? self::DEFAULT_ALLOWED_TYPES;
        $this->errorMessages = self::DEFAULT_ERROR_MESSAGES;
        $this->ffmpegConfig = array_merge([
            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
            'timeout' => 3600,
            'ffmpeg.threads' => 12,
        ], $ffmpegConfig);
    }

    /**
     * Set the upload directory with validation
     */
    public function setUploadDir(string $directory): self
    {
        $directory = rtrim($directory, '/') . '/';

        if (!is_dir($directory)) {
            $this->ensureUploadDirectoryExists($directory);
        }

        if (!is_writable($directory)) {
            throw new RuntimeException(sprintf($this->errorMessages['directory_creation'], $directory));
        }

        $this->uploadDir = $directory;
        return $this;
    }

    /**
     * Ensure upload directory exists and is writable
     */
    protected function ensureUploadDirectoryExists(string $directory): void
    {
        if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf($this->errorMessages['directory_creation'], $directory));
        }
    }

    /**
     * Upload a video file
     */
    public function upload(string $fileInputName, bool $rename = true, ?string $customName = null): array
    {
        if (!isset($_FILES[$fileInputName])) {
            throw new InvalidArgumentException(sprintf('File input "%s" does not exist in $_FILES', $fileInputName));
        }

        $file = $_FILES[$fileInputName];

        try {
            $this->validateVideoFile($file);

            $filename = $this->generateFilename($file['name'], $rename, $customName);
            $filePath = $this->uploadDir . $filename;

            $this->checkExistingFile($filePath);

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new RuntimeException($this->errorMessages['upload_failed']);
            }

            // Get video metadata
            $metadata = $this->getVideoMetadata($filePath);

            return [
                'success' => true,
                'file' => $filePath,
                'filename' => $filename,
                'size' => $file['size'],
                'type' => $file['type'],
                'metadata' => $metadata,
                'message' => 'Video uploaded successfully'
            ];
        } catch (Exception $e) {
            $this->logError($file['name'] ?? '', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a thumbnail from the video
     */
    public function generateThumbnail(
        string $videoPath,
        string $outputPath = null,
        int $time = 5,
        int $width = 320,
        int $height = 240
    ): string {
        if (!file_exists($videoPath)) {
            throw new RuntimeException(sprintf($this->errorMessages['file_not_found'], $videoPath));
        }

        $outputPath = $outputPath ?? $this->uploadDir . 'thumbnails/' . pathinfo($videoPath, PATHINFO_FILENAME) . '.jpg';
        $this->ensureUploadDirectoryExists(dirname($outputPath));

        $command = sprintf(
            '%s -ss %d -i %s -vframes 1 -s %dx%d -f image2 %s 2>&1',
            $this->ffmpegConfig['ffmpeg.binaries'],
            $time,
            escapeshellarg($videoPath),
            $width,
            $height,
            escapeshellarg($outputPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            throw new RuntimeException($this->errorMessages['thumbnail_failed'] . ': ' . implode("\n", $output));
        }

        return $outputPath;
    }

    /**
     * Get video metadata (duration, dimensions, etc.)
     */
    public function getVideoMetadata(string $videoPath): array
    {
        if (!file_exists($videoPath)) {
            throw new RuntimeException(sprintf($this->errorMessages['file_not_found'], $videoPath));
        }

        $command = sprintf(
            '%s -v error -show_entries format=duration:stream=width,height,codec_name -of json %s',
            $this->ffmpegConfig['ffprobe.binaries'],
            escapeshellarg($videoPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            return ['error' => 'Could not retrieve video metadata'];
        }

        $metadata = json_decode(implode("\n", $output), true);

        return [
            'duration' => $metadata['format']['duration'] ?? 0,
            'width' => $metadata['streams'][0]['width'] ?? 0,
            'height' => $metadata['streams'][0]['height'] ?? 0,
            'codec' => $metadata['streams'][0]['codec_name'] ?? 'unknown',
            'format' => pathinfo($videoPath, PATHINFO_EXTENSION)
        ];
    }

    /**
     * Validate video file before upload
     */
    protected function validateVideoFile(array $file): bool
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException($this->getUploadErrorMessage($file['error']));
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new RuntimeException(sprintf($this->errorMessages['size_exceeded'], $this->maxFileSize));
        }

        // Ensure the file exists and can be accessed
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Uploaded file is missing or inaccessible');
        }

        // Validate the MIME type
        $fileMimeType = $this->getFileMimeType($file['tmp_name']);
        if (empty($fileMimeType)) {
            throw new RuntimeException('Unable to determine file MIME type');
        }

        // Check if MIME type is allowed
        if (!in_array($fileMimeType, $this->allowedMimeTypes, true)) {
            throw new RuntimeException(sprintf($this->errorMessages['invalid_type'], implode(', ', $this->allowedMimeTypes)));
        }

        return true;
    }

    /**
     * Get MIME type of a file
     */
    protected function getFileMimeType(string $filePath): string
    {
        // First try finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        // Fallback to mime_content_type if finfo fails
        if (empty($mime)) {
            $mime = mime_content_type($filePath);
        }

        return $mime ?? '';
    }

    /**
     * Generate a filename for the uploaded file
     */
    protected function generateFilename(string $originalName, bool $rename, ?string $customName = null): string
    {
        if ($customName !== null) {
            return $customName . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
        }

        return $rename ? uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($originalName, PATHINFO_EXTENSION)
                      : $originalName;
    }

    /**
     * Check if file exists and handle accordingly
     */
    protected function checkExistingFile(string $filePath): void
    {
        if (file_exists($filePath) && !$this->overwriteExisting) {
            throw new RuntimeException(sprintf($this->errorMessages['file_exists'], $filePath));
        }
    }

    /**
     * Get human-readable upload error message
     */
    protected function getUploadErrorMessage(int $errorCode): string
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

    /**
     * Log an error message
     */
    protected function logError(string $filename, string $error): void
    {
        $logFile = $this->uploadDir . 'video_upload_errors.log';
        $logMessage = sprintf("[%s] ERROR - %s: %s\n", date('Y-m-d H:i:s'), $filename, $error);
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Set whether to overwrite existing files
     */
    public function setOverwrite(bool $overwrite): self
    {
        $this->overwriteExisting = $overwrite;
        return $this;
    }

    /**
     * Set allowed MIME types
     */
    public function setAllowedMimeTypes(array $types): self
    {
        $this->allowedMimeTypes = $types;
        return $this;
    }

    /**
     * Set maximum file size
     */
    public function setMaxFileSize(int $size): self
    {
        $this->maxFileSize = $size;
        return $this;
    }

    /**
     * Set custom error messages
     */
    public function setErrorMessages(array $messages): self
    {
        $this->errorMessages = array_merge($this->errorMessages, $messages);
        return $this;
    }

    /**
     * Check if FFmpeg is available
     */
    public function isFfmpegAvailable(): bool
    {
        exec(sprintf('%s -version', $this->ffmpegConfig['ffmpeg.binaries']), $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Compress/transcode video file
     */
    public function compressVideo(
        string $inputPath,
        string $outputPath = null,
        int $crf = 28,
        string $preset = 'medium'
    ): string {
        if (!file_exists($inputPath)) {
            throw new RuntimeException(sprintf($this->errorMessages['file_not_found'], $inputPath));
        }

        $outputPath = $outputPath ?? $this->uploadDir . 'compressed/' . pathinfo($inputPath, PATHINFO_BASENAME);
        $this->ensureUploadDirectoryExists(dirname($outputPath));

        $command = sprintf(
            '%s -i %s -c:v libx264 -preset %s -crf %d -c:a copy %s 2>&1',
            $this->ffmpegConfig['ffmpeg.binaries'],
            escapeshellarg($inputPath),
            escapeshellarg($preset),
            $crf,
            escapeshellarg($outputPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            throw new RuntimeException('Video compression failed: ' . implode("\n", $output));
        }

        return $outputPath;
    }

    /**
     * Extract a segment from a video
     */
    public function extractSegment(
        string $inputPath,
        int $startTime,
        int $duration,
        string $outputPath = null
    ): string {
        if (!file_exists($inputPath)) {
            throw new RuntimeException(sprintf($this->errorMessages['file_not_found'], $inputPath));
        }

        $outputPath = $outputPath ?? $this->uploadDir . 'segments/' . pathinfo($inputPath, PATHINFO_FILENAME) . '_segment.mp4';
        $this->ensureUploadDirectoryExists(dirname($outputPath));

        $command = sprintf(
            '%s -i %s -ss %d -t %d -c copy %s 2>&1',
            $this->ffmpegConfig['ffmpeg.binaries'],
            escapeshellarg($inputPath),
            $startTime,
            $duration,
            escapeshellarg($outputPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($outputPath)) {
            throw new RuntimeException('Video segment extraction failed: ' . implode("\n", $output));
        }

        return $outputPath;
    }
}