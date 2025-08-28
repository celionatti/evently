<?php

declare(strict_types=1);

namespace Trees\Helper\Support;

use GdImage;
use InvalidArgumentException;
use RuntimeException;

/**
 * =========================================
 * *****************************************
 * ========== Trees Image Class ============
 * *****************************************
 * =========================================
 */

class Image
{
    protected array $errorMessages = [
        'file_not_found' => 'File not found: %s',
        'unsupported_format' => 'Unsupported image format: %s',
        'image_load_failed' => 'Failed to load image: %s',
        'image_save_failed' => 'Failed to save image: %s',
        'crop_failed' => 'Crop operation failed',
        'invalid_color' => 'Invalid color format: %s',
        'font_not_found' => 'Font file not found: %s',
        'invalid_position' => 'Invalid position: %s',
    ];

    protected int $jpegQuality = 85;
    protected int $pngCompression = 9;
    protected int $webpQuality = 85;

    /**
     * Set error messages
     */
    public function setErrorMessages(array $messages): self
    {
        $this->errorMessages = array_merge($this->errorMessages, $messages);
        return $this;
    }

    /**
     * Set image quality settings
     */
    public function setQualitySettings(?int $jpegQuality = null, ?int $pngCompression = null, ?int $webpQuality = null): self
    {
        if ($jpegQuality !== null) $this->jpegQuality = max(0, min(100, $jpegQuality));
        if ($pngCompression !== null) $this->pngCompression = max(0, min(9, $pngCompression));
        if ($webpQuality !== null) $this->webpQuality = max(0, min(100, $webpQuality));
        return $this;
    }

    /**
     * Resizes an image to fit within a specified maximum size while maintaining aspect ratio
     */
    public function resize(string $filename, int $max_size = 700, bool $preserveOriginal = false): string
    {
        $image = $this->loadImage($filename);
        [$src_w, $src_h] = $this->getImageDimensions($image);

        // Calculate new dimensions while maintaining aspect ratio
        [$dst_w, $dst_h] = $this->calculateDestinationSize($src_w, $src_h, $max_size);

        $dst_image = imagecreatetruecolor($dst_w, $dst_h);
        $this->handleAlphaChannel($dst_image);

        if (!imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h)) {
            throw new RuntimeException('Image resampling failed');
        }

        $outputFilename = $preserveOriginal ? $this->generateResizedFilename($filename) : $filename;
        $this->saveImage($dst_image, $outputFilename);
        $this->destroyImage($image, $dst_image);

        return $outputFilename;
    }

    /**
     * Adds a watermark to the image with position and opacity control
     */
    public function watermark(
        string $filename,
        string $watermarkPath,
        string $position = 'bottom-right',
        int $opacity = 50,
        int $offsetX = 10,
        int $offsetY = 10
    ): string {
        $image = $this->loadImage($filename);
        $watermark = $this->loadImage($watermarkPath);

        $this->applyWatermark($image, $watermark, $position, $opacity, $offsetX, $offsetY);

        $this->saveImage($image, $filename);
        $this->destroyImage($image, $watermark);

        return $filename;
    }

    /**
     * Crops an image to specified dimensions from a starting point
     */
    public function crop(string $filename, int $width, int $height, int $x = 0, int $y = 0): string
    {
        $image = $this->loadImage($filename);
        [$src_w, $src_h] = $this->getImageDimensions($image);

        // Validate crop dimensions
        if (
            $x < 0 || $y < 0 || $width <= 0 || $height <= 0 ||
            ($x + $width) > $src_w || ($y + $height) > $src_h
        ) {
            throw new InvalidArgumentException('Invalid crop dimensions');
        }

        $dst_image = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);

        if ($dst_image === false) {
            throw new RuntimeException($this->errorMessages['crop_failed']);
        }

        $this->saveImage($dst_image, $filename);
        $this->destroyImage($image, $dst_image);

        return $filename;
    }

    /**
     * Converts the image to grayscale
     */
    public function grayscale(string $filename): string
    {
        $image = $this->loadImage($filename);

        if (!imagefilter($image, IMG_FILTER_GRAYSCALE)) {
            throw new RuntimeException('Failed to apply grayscale filter');
        }

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Rotates the image by specified degrees with background color
     */
    public function rotate(string $filename, int $degrees = 90, string $bgColor = '#000000'): string
    {
        $image = $this->loadImage($filename);
        $rgb = $this->hexToRgb($bgColor);
        $bgColor = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);

        $rotatedImage = imagerotate($image, $degrees, $bgColor);

        if ($rotatedImage === false) {
            throw new RuntimeException('Image rotation failed');
        }

        $this->saveImage($rotatedImage, $filename);
        $this->destroyImage($image, $rotatedImage);

        return $filename;
    }

    /**
     * Flips the image horizontally or vertically
     */
    public function flip(string $filename, string $mode = 'horizontal'): string
    {
        $image = $this->loadImage($filename);

        $flipMode = match (strtolower($mode)) {
            'horizontal' => IMG_FLIP_HORIZONTAL,
            'vertical' => IMG_FLIP_VERTICAL,
            default => throw new InvalidArgumentException(sprintf($this->errorMessages['invalid_position'], $mode)),
        };

        if (!imageflip($image, $flipMode)) {
            throw new RuntimeException('Image flip failed');
        }

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Adds a border to the image
     */
    public function addBorder(string $filename, string $color = '#000000', int $thickness = 10): string
    {
        $image = $this->loadImage($filename);
        [$width, $height] = $this->getImageDimensions($image);

        $rgb = $this->hexToRgb($color);
        $borderColor = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);

        // Draw border on all four sides
        for ($i = 0; $i < $thickness; $i++) {
            imagerectangle($image, $i, $i, $width - $i - 1, $height - $i - 1, $borderColor);
        }

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Applies a specified image filter
     */
    public function applyFilter(string $filename, int $filterType, ...$args): string
    {
        $image = $this->loadImage($filename);

        if (!imagefilter($image, $filterType, ...$args)) {
            throw new RuntimeException('Failed to apply image filter');
        }

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Applies Gaussian blur to the image
     */
    public function blur(string $filename, int $intensity = 5): string
    {
        $image = $this->loadImage($filename);

        for ($i = 0; $i < $intensity; $i++) {
            if (!imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR)) {
                throw new RuntimeException('Failed to apply blur filter');
            }
        }

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Adds text watermark to the image
     */
    public function addTextWatermark(
        string $filename,
        string $text,
        string $fontFile,
        int $fontSize = 20,
        string $color = '#000000',
        string $position = 'bottom-right',
        int $opacity = 100,
        int $angle = 0,
        int $padding = 10
    ): string {
        if (!file_exists($fontFile)) {
            throw new InvalidArgumentException(sprintf($this->errorMessages['font_not_found'], $fontFile));
        }

        $image = $this->loadImage($filename);
        $rgb = $this->hexToRgb($color);
        $textColor = imagecolorallocatealpha($image, $rgb['r'], $rgb['g'], $rgb['b'], 127 - (int)($opacity * 1.27));

        [$x, $y] = $this->calculateTextPosition($position, $fontSize, $fontFile, $text, $image, $angle, $padding);

        if (!imagettftext($image, $fontSize, $angle, $x, $y, $textColor, $fontFile, $text)) {
            throw new RuntimeException('Failed to add text watermark');
        }

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        return $filename;
    }

    /**
     * Resizes an image to specific dimensions (may distort aspect ratio)
     */
    public function resizeToDimensions(string $filename, int $width, int $height, bool $preserveOriginal = false): string
    {
        $image = $this->loadImage($filename);
        [$src_w, $src_h] = $this->getImageDimensions($image);

        $dst_image = imagecreatetruecolor($width, $height);
        $this->handleAlphaChannel($dst_image);

        if (!imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $width, $height, $src_w, $src_h)) {
            throw new RuntimeException('Image resampling failed');
        }

        $outputFilename = $preserveOriginal ? $this->generateResizedFilename($filename) : $filename;
        $this->saveImage($dst_image, $outputFilename);
        $this->destroyImage($image, $dst_image);

        return $outputFilename;
    }

    /**
     * Resizes an image to a specific height while maintaining aspect ratio
     */
    public function resizeToHeight(string $filename, int $height, bool $preserveOriginal = false): string
    {
        $image = $this->loadImage($filename);
        [$src_w, $src_h] = $this->getImageDimensions($image);

        $ratio = $height / $src_h;
        $width = (int)($src_w * $ratio);

        $dst_image = imagecreatetruecolor($width, $height);
        $this->handleAlphaChannel($dst_image);

        if (!imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $width, $height, $src_w, $src_h)) {
            throw new RuntimeException('Image resampling failed');
        }

        $outputFilename = $preserveOriginal ? $this->generateResizedFilename($filename) : $filename;
        $this->saveImage($dst_image, $outputFilename);
        $this->destroyImage($image, $dst_image);

        return $outputFilename;
    }

    /**
     * Resizes an image to fit within specified dimensions while maintaining aspect ratio
     */
    public function resizeWithinDimensions(string $filename, int $maxWidth, int $maxHeight, bool $preserveOriginal = false): string
    {
        $image = $this->loadImage($filename);
        [$src_w, $src_h] = $this->getImageDimensions($image);

        // Calculate new dimensions while maintaining aspect ratio
        $ratio = min($maxWidth / $src_w, $maxHeight / $src_h);
        $width = (int)($src_w * $ratio);
        $height = (int)($src_h * $ratio);

        $dst_image = imagecreatetruecolor($width, $height);
        $this->handleAlphaChannel($dst_image);

        if (!imagecopyresampled($dst_image, $image, 0, 0, 0, 0, $width, $height, $src_w, $src_h)) {
            throw new RuntimeException('Image resampling failed');
        }

        $outputFilename = $preserveOriginal ? $this->generateResizedFilename($filename) : $filename;
        $this->saveImage($dst_image, $outputFilename);
        $this->destroyImage($image, $dst_image);

        return $outputFilename;
    }

    /**
     * Converts an image to WebP format
     */
    public function convertToWebp(string $filename): string
    {
        if (!function_exists('imagewebp')) {
            throw new RuntimeException('WebP support is not available in this PHP installation');
        }

        $image = $this->loadImage($filename);
        $newFilename = pathinfo($filename, PATHINFO_DIRNAME) . '/' .
            pathinfo($filename, PATHINFO_FILENAME) . '.webp';

        // Create a truecolor image to ensure best quality conversion
        $width = imagesx($image);
        $height = imagesy($image);
        $webpImage = imagecreatetruecolor($width, $height);

        // Preserve transparency for PNG/GIF
        if (imageistruecolor($image)) {
            imagealphablending($webpImage, false);
            imagesavealpha($webpImage, true);
        }

        imagecopy($webpImage, $image, 0, 0, 0, 0, $width, $height);

        if (!imagewebp($webpImage, $newFilename, $this->webpQuality)) {
            imagedestroy($webpImage);
            throw new RuntimeException('Failed to convert image to WebP');
        }

        $this->destroyImage($image, $webpImage);
        return $newFilename;
    }

    /**
     * Applies various optimizations to reduce file size
     */
    public function optimize(string $filename): string
    {
        $mime = mime_content_type($filename);
        if ($mime === false) {
            throw new InvalidArgumentException('Unable to determine image MIME type');
        }

        $image = $this->loadImage($filename);

        // For PNG images, apply more aggressive optimization
        if ($mime === 'image/png') {
            // Convert truecolor to palette if possible (reduces size significantly)
            if (imageistruecolor($image) && $this->canConvertToPalette($image)) {
                imagetruecolortopalette($image, false, 255);
            }

            // Reduce color depth for palette images
            if (!imageistruecolor($image)) {
                imagetruecolortopalette($image, true, 128);
            }
        }

        // For JPEG, create a clean copy without EXIF data
        if ($mime === 'image/jpeg') {
            $width = imagesx($image);
            $height = imagesy($image);
            $newImage = imagecreatetruecolor($width, $height);
            imagefill($newImage, 0, 0, imagecolorallocate($newImage, 255, 255, 255));
            imagecopy($newImage, $image, 0, 0, 0, 0, $width, $height);
            $this->destroyImage($image);
            $image = $newImage;
        }

        $this->saveImage($image, $filename);
        $this->destroyImage($image);

        // Run external optimizers if available
        $this->runExternalOptimizers($filename);

        return $filename;
    }

    /**
     * Checks if an image can be safely converted to palette mode
     */
    private function canConvertToPalette(GdImage $image): bool
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Sample some pixels to check if image has more than 256 colors
        $colors = [];
        $sampleSize = min(50, $width, $height); // Sample up to 50x50 pixels

        for ($x = 0; $x < $sampleSize; $x += max(1, $width / $sampleSize)) {
            for ($y = 0; $y < $sampleSize; $y += max(1, $height / $sampleSize)) {
                $color = imagecolorat($image, (int)$x, (int)$y);
                $colors[$color] = true;
                if (count($colors) > 255) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Runs external optimization tools if available
     */
    private function runExternalOptimizers(string $filename): void
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        try {
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    // Try jpegoptim if available
                    if ($this->isCommandAvailable('jpegoptim')) {
                        $quality = max(70, $this->jpegQuality - 5); // Allow slight quality reduction
                        exec("jpegoptim --strip-all --max={$quality} " . escapeshellarg($filename));
                    }
                    break;

                case 'png':
                    // Try optipng if available
                    if ($this->isCommandAvailable('optipng')) {
                        exec("optipng -o{$this->pngCompression} " . escapeshellarg($filename));
                    }
                    // Try pngquant if available
                    elseif ($this->isCommandAvailable('pngquant')) {
                        $quality = max(60, 100 - ($this->pngCompression * 10));
                        exec("pngquant --quality={$quality}-100 --force --output " .
                            escapeshellarg($filename) . " " . escapeshellarg($filename));
                    }
                    break;

                case 'webp':
                    // Try cwebp if available
                    if ($this->isCommandAvailable('cwebp')) {
                        exec("cwebp -q {$this->webpQuality} " . escapeshellarg($filename) .
                            " -o " . escapeshellarg($filename));
                    }
                    break;
            }
        } catch (\Exception $e) {
            // Silently fail external optimizations - they're optional
        }
    }

    /**
     * Checks if a command line tool is available
     */
    private function isCommandAvailable(string $command): bool
    {
        if (in_array(strtoupper(substr(PHP_OS, 0, 3)), ['WIN'])) {
            // On Windows, we need to check differently
            $where = `where $command 2>nul`;
            return !empty($where);
        }

        $which = `which $command`;
        return !empty($which);
    }

    /**
     * Converts hex color to RGB
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (!preg_match('/^[0-9a-f]{3,6}$/i', $hex)) {
            throw new InvalidArgumentException(sprintf($this->errorMessages['invalid_color'], $hex));
        }

        if (strlen($hex) === 3) {
            $r = hexdec($hex[0] . $hex[0]);
            $g = hexdec($hex[1] . $hex[1]);
            $b = hexdec($hex[2] . $hex[2]);
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return ['r' => $r, 'g' => $g, 'b' => $b];
    }

    /**
     * Loads image from file with format detection
     */
    private function loadImage(string $filename): GdImage
    {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(sprintf($this->errorMessages['file_not_found'], $filename));
        }

        $mime = mime_content_type($filename);
        if ($mime === false) {
            throw new InvalidArgumentException(sprintf($this->errorMessages['unsupported_format'], $filename));
        }

        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($filename),
            'image/png' => imagecreatefrompng($filename),
            'image/gif' => imagecreatefromgif($filename),
            'image/webp' => imagecreatefromwebp($filename),
            default => throw new InvalidArgumentException(sprintf($this->errorMessages['unsupported_format'], $filename)),
        };

        if ($image === false) {
            throw new InvalidArgumentException(sprintf($this->errorMessages['image_load_failed'], $filename));
        }

        return $image;
    }

    /**
     * Saves image to file with format detection
     */
    private function saveImage(GdImage $image, string $filename): void
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        imageinterlace($image, true);
        $result = match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $filename, $this->jpegQuality),
            'png' => imagepng($image, $filename, $this->pngCompression),
            'gif' => imagegif($image, $filename),
            'webp' => imagewebp($image, $filename, $this->webpQuality),
            default => throw new InvalidArgumentException(sprintf($this->errorMessages['unsupported_format'], $filename)),
        };

        if ($result === false) {
            throw new RuntimeException(sprintf($this->errorMessages['image_save_failed'], $filename));
        }
    }

    /**
     * Destroys image resources
     */
    private function destroyImage(GdImage ...$images): void
    {
        foreach ($images as $image) {
            if ($image instanceof GdImage) {
                imagedestroy($image);
            }
        }
    }

    /**
     * Handles alpha channel for transparency
     */
    private function handleAlphaChannel(GdImage $image): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    /**
     * Calculates destination size while maintaining aspect ratio
     */
    private function calculateDestinationSize(int $src_w, int $src_h, int $max_size): array
    {
        if ($src_w <= $max_size && $src_h <= $max_size) {
            return [$src_w, $src_h];
        }

        $ratio = $src_w / $src_h;

        if ($ratio > 1) {
            $dst_w = $max_size;
            $dst_h = (int)($max_size / $ratio);
        } else {
            $dst_h = $max_size;
            $dst_w = (int)($max_size * $ratio);
        }

        return [$dst_w, $dst_h];
    }

    /**
     * Applies watermark to image
     */
    private function applyWatermark(
        GdImage $image,
        GdImage $watermark,
        string $position,
        int $opacity,
        int $offsetX,
        int $offsetY
    ): void {
        [$src_w, $src_h] = $this->getImageDimensions($image);
        [$wm_w, $wm_h] = $this->getImageDimensions($watermark);
        [$dst_x, $dst_y] = $this->calculatePosition($position, $src_w, $src_h, $wm_w, $wm_h, $offsetX, $offsetY);

        if (!imagecopymerge($image, $watermark, $dst_x, $dst_y, 0, 0, $wm_w, $wm_h, $opacity)) {
            throw new RuntimeException('Failed to apply watermark');
        }
    }

    /**
     * Calculates position for watermark or text
     */
    private function calculatePosition(
        string $position,
        int $src_w,
        int $src_h,
        int $wm_w,
        int $wm_h,
        int $offsetX = 10,
        int $offsetY = 10
    ): array {
        return match (strtolower($position)) {
            'top-left' => [$offsetX, $offsetY],
            'top-right' => [$src_w - $wm_w - $offsetX, $offsetY],
            'bottom-left' => [$offsetX, $src_h - $wm_h - $offsetY],
            'bottom-right' => [$src_w - $wm_w - $offsetX, $src_h - $wm_h - $offsetY],
            'center' => [($src_w - $wm_w) / 2, ($src_h - $wm_h) / 2],
            default => throw new InvalidArgumentException(sprintf($this->errorMessages['invalid_position'], $position)),
        };
    }

    /**
     * Calculates text position
     */
    private function calculateTextPosition(
        string $position,
        int $fontSize,
        string $fontFile,
        string $text,
        GdImage $image,
        int $angle = 0,
        int $padding = 10
    ): array {
        $textBox = imagettfbbox($fontSize, $angle, $fontFile, $text);
        if ($textBox === false) {
            throw new RuntimeException('Failed to calculate text bounding box');
        }

        $textWidth = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);

        return $this->calculatePosition(
            $position,
            $imageWidth,
            $imageHeight,
            $textWidth,
            $textHeight,
            $padding,
            $padding + $textHeight
        );
    }

    /**
     * Gets image dimensions
     */
    private function getImageDimensions(GdImage $image): array
    {
        return [imagesx($image), imagesy($image)];
    }

    /**
     * Generates a filename for resized images
     */
    private function generateResizedFilename(string $originalFilename): string
    {
        $pathinfo = pathinfo($originalFilename);
        return $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '_resized.' . $pathinfo['extension'];
    }
}
