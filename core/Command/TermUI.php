<?php

declare(strict_types=1);

namespace Trees\Command;

class TermUI
{
    // ANSI color codes
    const RESET = "\033[0m";
    const BOLD = "\033[1m";
    const DIM = "\033[2m";
    const ITALIC = "\033[3m";
    const UNDERLINE = "\033[4m";
    const BLINK = "\033[5m";
    const REVERSE = "\033[7m";
    const HIDDEN = "\033[8m";

    // Foreground colors
    const BLACK = "\033[30m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const MAGENTA = "\033[35m";
    const CYAN = "\033[36m";
    const WHITE = "\033[37m";
    const DEFAULT = "\033[39m";

    // Background colors
    const BG_BLACK = "\033[40m";
    const BG_RED = "\033[41m";
    const BG_GREEN = "\033[42m";
    const BG_YELLOW = "\033[43m";
    const BG_BLUE = "\033[44m";
    const BG_MAGENTA = "\033[45m";
    const BG_CYAN = "\033[46m";
    const BG_WHITE = "\033[47m";
    const BG_DEFAULT = "\033[49m";

    // 8-bit and RGB colors
    public static function fgColor(int $r, int $g, int $b): string {
        return "\033[38;2;{$r};{$g};{$b}m";
    }

    public static function bgColor(int $r, int $g, int $b): string {
        return "\033[48;2;{$r};{$g};{$b}m";
    }

    /**
     * Draw a modern box with a title and content
     */
    public static function box(string $title, string $content, string $color = self::GREEN, string $titleColor = null): void
    {
        $titleColor = $titleColor ?? $color;
        $lines = explode(PHP_EOL, $content);
        $width = 0;

        // Find the maximum line length
        foreach ($lines as $line) {
            $width = max($width, mb_strlen(self::stripAnsi($line)));
        }

        // Ensure minimum width for title
        $titleLength = mb_strlen(self::stripAnsi($title));
        $width = max($width, $titleLength + 8);

        // Box drawing
        $topBorder = $color . "╭" . str_repeat("─", $width + 2) . "╮" . self::RESET;
        $titleLine = $color . "│ " . $titleColor . self::BOLD . " " . $title . " " . $color . str_repeat(" ", $width - $titleLength - 1) . " │" . self::RESET;
        $middleBorder = $color . "├" . str_repeat("─", $width + 2) . "┤" . self::RESET;
        $bottomBorder = $color . "╰" . str_repeat("─", $width + 2) . "╯" . self::RESET;

        echo $topBorder . PHP_EOL;
        echo $titleLine . PHP_EOL;
        echo $middleBorder . PHP_EOL;

        foreach ($lines as $line) {
            $lineLength = mb_strlen(self::stripAnsi($line));
            $padding = str_repeat(" ", $width - $lineLength);
            echo $color . "│ " . self::RESET . $line . $padding . $color . " │" . self::RESET . PHP_EOL;
        }

        echo $bottomBorder . PHP_EOL;
    }

    /**
     * Create a modern selection menu with highlighted selection
     */
    public static function select(string $question, array $options, string $color = self::CYAN): ?string
    {
        $maxOptionLength = 0;
        foreach ($options as $option) {
            $maxOptionLength = max($maxOptionLength, mb_strlen(self::stripAnsi($option)));
        }

        $boxWidth = $maxOptionLength + 10;
        $questionLength = mb_strlen(self::stripAnsi($question));
        $boxWidth = max($boxWidth, $questionLength + 8);

        echo $color . "╭" . str_repeat("─", $boxWidth) . "╮" . self::RESET . PHP_EOL;
        echo $color . "│ " . self::BOLD . $question . str_repeat(" ", $boxWidth - $questionLength - 2) . $color . "│" . self::RESET . PHP_EOL;
        echo $color . "├" . str_repeat("─", $boxWidth) . "┤" . self::RESET . PHP_EOL;

        foreach ($options as $key => $option) {
            $padding = str_repeat(" ", $boxWidth - mb_strlen(self::stripAnsi($option)) - 6);
            echo $color . "│ " . self::RESET . "[{$color}" . self::BOLD . $key . self::RESET . "] " . $option . $padding . $color . "│" . self::RESET . PHP_EOL;
        }

        echo $color . "╰" . str_repeat("─", $boxWidth) . "╯" . self::RESET . PHP_EOL;

        echo $color . "➤ Select: " . self::RESET;
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);

        return $options[$line] ?? null;
    }

    /**
     * Modern prompt with optional default value
     */
    public static function prompt(string $question, $default = null, string $color = self::BLUE)
    {
        $defaultText = $default !== null ? " [" . self::DIM . $default . self::RESET . $color . "]" : "";
        echo $color . "▸ " . $question . $defaultText . ": " . self::RESET;

        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);

        return $line !== "" ? $line : $default;
    }

    /**
     * Output a success message with checkmark
     */
    public static function success(string $message): void
    {
        self::box("✓ SUCCESS", $message, self::GREEN, self::BG_GREEN . self::BLACK);
    }

    /**
     * Output an error message with cross mark
     */
    public static function error(string $message): void
    {
        self::box("✗ ERROR", $message, self::RED, self::BG_RED . self::WHITE);
    }

    /**
     * Output an info message with info symbol
     */
    public static function info(string $message): void
    {
        self::box("ℹ INFO", $message, self::BLUE, self::BG_BLUE . self::WHITE);
    }

    /**
     * Output a warning message with warning symbol
     */
    public static function warning(string $message): void
    {
        self::box("⚠ WARNING", $message, self::YELLOW, self::BG_YELLOW . self::BLACK);
    }

    /**
     * Strip ANSI color codes from string
     */
    private static function stripAnsi(string $string): string
    {
        return preg_replace("/\033\[[^m]*m/", "", $string);
    }

    /**
     * Modern progress bar
     */
    public static function progress(int $current, int $total, int $width = 50): void
    {
        $percent = ($current / $total);
        $filled = (int) round($width * $percent);
        $empty = $width - $filled;

        $bar = self::BG_GREEN . str_repeat(" ", $filled) . self::BG_DEFAULT . self::RED . str_repeat(" ", $empty);
        $percentage = sprintf(" %.1f%% ", $percent * 100);

        echo "\r[" . $bar . self::RESET . "] " . self::BOLD . $percentage . self::RESET;

        if ($current === $total) {
            echo PHP_EOL;
        }
    }
}