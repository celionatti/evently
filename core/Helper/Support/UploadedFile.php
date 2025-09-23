<?php

declare(strict_types=1);

namespace Trees\Helper\Support;

use RuntimeException;
use InvalidArgumentException;

/**
 * =========================================
 * *****************************************
 * ======= Trees UploadedFile Class ========
 * *****************************************
 * =========================================
 */

class UploadedFile
{
    private string $path;
    private string $originalName;
    private string $mimeType;
    private int $error;
    private int $size;
    private ?string $uploadedPath = null;

    public function __construct(
        string $path,
        string $originalName,
        string $mimeType,
        int $error,
        int $size
    ) {
        if ($error === UPLOAD_ERR_OK && !is_uploaded_file($path)) {
            throw new InvalidArgumentException('The file was not uploaded via HTTP POST');
        }

        $this->path = $path;
        $this->originalName = $originalName;
        $this->mimeType = $mimeType;
        $this->error = $error;
        $this->size = $size;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getClientOriginalName(): string
    {
        return $this->originalName;
    }

    public function getClientMimeType(): string
    {
        return $this->mimeType;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    public function hasFile(): bool
    {
        return $this->error !== UPLOAD_ERR_NO_FILE;
    }

    public function getErrorMessage(): string
    {
        static $errors = [
            UPLOAD_ERR_OK => 'There is no error, the file uploaded with success',
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];

        return $errors[$this->error] ?? 'Unknown upload error';
    }

    public function getExtension(): string
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }

    public function move(string $directory, ?string $name = null): string
    {
        if (!$this->isValid()) {
            throw new RuntimeException(
                'Cannot move the file: ' . $this->getErrorMessage()
            );
        }

        $targetPath = rtrim($directory, '/') . '/' . ($name ?: $this->originalName);

        if (!@move_uploaded_file($this->path, $targetPath)) {
            throw new RuntimeException(sprintf(
                'Could not move the file "%s" to "%s"',
                $this->path,
                $targetPath
            ));
        }

        $this->uploadedPath = $targetPath;
        return $targetPath;
    }

    public function getUploadedPath(): ?string
    {
        return $this->uploadedPath;
    }

    public function getContents(): string
    {
        if (!$this->isValid()) {
            throw new RuntimeException('Cannot get contents of an invalid uploaded file');
        }

        $contents = file_get_contents($this->path);
        if ($contents === false) {
            throw new RuntimeException('Could not read the uploaded file');
        }

        return $contents;
    }
}
