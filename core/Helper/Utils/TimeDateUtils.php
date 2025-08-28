<?php

declare(strict_types=1);

namespace Trees\Helper\Utils;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use DateInterval;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

/**
 * =========================================
 * *****************************************
 * ======== Trees TimeDateUtils Class ======
 * *****************************************
 * A comprehensive date and time manipulation utility with:
 * - Timezone support
 * - Date arithmetic
 * - Multiple output formats
 * - Comparison operations
 * - Human-readable time differences
 *
 * Features:
 * - Fluent interface
 * - Immutable operations option
 * - Comprehensive timezone handling
 * - Exception-safe operations
 * =========================================
 */

class TimeDateUtils
{
    private DateTimeInterface $dateTime;
    private bool $immutable;

    /**
     * Constructor for TimeDateUtils
     *
     * @param string|DateTimeInterface $dateTime DateTime string or object (default: 'now')
     * @param string|DateTimeZone $timeZone Timezone string or object (default: 'UTC')
     * @param bool $immutable Use immutable DateTime objects (default: false)
     *
     * @throws InvalidArgumentException If date/time parsing fails
     */
    public function __construct(
        $dateTime = 'now',
        $timeZone = 'UTC',
        bool $immutable = false
    ) {
        $this->immutable = $immutable;
        $timeZone = $timeZone instanceof DateTimeZone ? $timeZone : new DateTimeZone($timeZone);

        try {
            if ($dateTime instanceof DateTimeInterface) {
                $this->dateTime = $immutable
                    ? DateTimeImmutable::createFromInterface($dateTime)
                    : new DateTime('@' . $dateTime->getTimestamp());
                $this->dateTime->setTimezone($timeZone);
            } else {
                $this->dateTime = $immutable
                    ? new DateTimeImmutable($dateTime, $timeZone)
                    : new DateTime($dateTime, $timeZone);
            }
        } catch (Exception $e) {
            throw new InvalidArgumentException(
                "Invalid date/time parameters: " . $e->getMessage()
            );
        }
    }

    /**
     * Create a new instance (fluent interface)
     */
    public static function create(
        $dateTime = 'now',
        $timeZone = 'UTC',
        bool $immutable = false
    ): self {
        return new static($dateTime, $timeZone, $immutable);
    }

    /**
     * Create from Unix timestamp
     */
    public static function fromTimestamp(
        int $timestamp,
        $timeZone = 'UTC',
        bool $immutable = false
    ): self {
        $dateTime = $immutable
            ? DateTimeImmutable::createFromFormat('U', (string)$timestamp)
            : DateTime::createFromFormat('U', (string)$timestamp);

        if ($dateTime === false) {
            throw new InvalidArgumentException("Invalid timestamp provided");
        }

        return new static($dateTime, $timeZone, $immutable);
    }

    /**
     * Create from format string
     */
    public static function fromFormat(
        string $format,
        string $dateTime,
        $timeZone = 'UTC',
        bool $immutable = false
    ): self {
        $dateTimeObj = $immutable
            ? DateTimeImmutable::createFromFormat($format, $dateTime)
            : DateTime::createFromFormat($format, $dateTime);

        if ($dateTimeObj === false) {
            throw new InvalidArgumentException(
                "Could not parse date/time from format '$format'"
            );
        }

        return new static($dateTimeObj, $timeZone, $immutable);
    }

    /**
     * Convert to human-readable "ago" format
     */
    public function toAgoFormat(?DateTimeInterface $relativeTo = null): string
    {
        $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
        $diff = $relativeTo->diff($this->dateTime);

        if ($diff->invert) {
            $periods = [
                'y' => ['year', 'years'],
                'm' => ['month', 'months'],
                'd' => ['day', 'days'],
                'h' => ['hour', 'hours'],
                'i' => ['minute', 'minutes'],
                's' => ['second', 'seconds']
            ];

            foreach ($periods as $key => $labels) {
                if ($diff->$key > 0) {
                    return sprintf(
                        '%d %s ago',
                        $diff->$key,
                        $diff->$key > 1 ? $labels[1] : $labels[0]
                    );
                }
            }
            return 'just now';
        }

        return $this->toRelativeFormat($relativeTo);
    }

    /**
     * Convert to human-readable "in future" format
     */
    public function toRelativeFormat(?DateTimeInterface $relativeTo = null): string
    {
        $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
        $diff = $relativeTo->diff($this->dateTime);

        $periods = [
            'y' => ['year', 'years'],
            'm' => ['month', 'months'],
            'd' => ['day', 'days'],
            'h' => ['hour', 'hours'],
            'i' => ['minute', 'minutes'],
            's' => ['second', 'seconds']
        ];

        foreach ($periods as $key => $labels) {
            if ($diff->$key > 0) {
                return sprintf(
                    'in %d %s',
                    $diff->$key,
                    $diff->$key > 1 ? $labels[1] : $labels[0]
                );
            }
        }

        return 'now';
    }

    /**
     * Format date for blog-style display
     */
    public function toBlogFormat(): string
    {
        return $this->dateTime->format('j F Y');
    }

    /**
     * Format with custom format string
     */
    public function toCustomFormat(string $format): string
    {
        return $this->dateTime->format($format);
    }

    /**
     * Get ISO 8601 formatted string
     */
    public function toISO8601(): string
    {
        return $this->dateTime->format(DateTimeInterface::ATOM);
    }

    /**
     * Get RFC 2822 formatted string
     */
    public function toRFC2822(): string
    {
        return $this->dateTime->format(DateTimeInterface::RFC2822);
    }

    /**
     * Get Unix timestamp
     */
    public function toUnixTimestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }

    /**
     * Add time interval
     */
    public function add(DateInterval $interval): self
    {
        $dateTime = clone $this->dateTime;
        $dateTime->add($interval);
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Subtract time interval
     */
    public function sub(DateInterval $interval): self
    {
        $dateTime = clone $this->dateTime;
        $dateTime->sub($interval);
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Add days
     */
    public function addDays(int $days): self
    {
        return $this->add(new DateInterval("P{$days}D"));
    }

    /**
     * Subtract days
     */
    public function subDays(int $days): self
    {
        return $this->sub(new DateInterval("P{$days}D"));
    }

    /**
     * Add hours
     */
    public function addHours(int $hours): self
    {
        return $this->add(new DateInterval("PT{$hours}H"));
    }

    /**
     * Subtract hours
     */
    public function subHours(int $hours): self
    {
        return $this->sub(new DateInterval("PT{$hours}H"));
    }

    /**
     * Add minutes
     */
    public function addMinutes(int $minutes): self
    {
        return $this->add(new DateInterval("PT{$minutes}M"));
    }

    /**
     * Subtract minutes
     */
    public function subMinutes(int $minutes): self
    {
        return $this->sub(new DateInterval("PT{$minutes}M"));
    }

    /**
     * Add seconds
     */
    public function addSeconds(int $seconds): self
    {
        return $this->add(new DateInterval("PT{$seconds}S"));
    }

    /**
     * Subtract seconds
     */
    public function subSeconds(int $seconds): self
    {
        return $this->sub(new DateInterval("PT{$seconds}S"));
    }

    /**
     * Set timezone
     */
    public function setTimezone($timeZone): self
    {
        $timeZone = $timeZone instanceof DateTimeZone
            ? $timeZone
            : new DateTimeZone($timeZone);

        $dateTime = clone $this->dateTime;
        $dateTime->setTimezone($timeZone);
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Compare with another date/time
     */
    public function compareTo($compareDate): int
    {
        $comparisonDate = $compareDate instanceof DateTimeInterface
            ? $compareDate
            : new DateTime($compareDate);

        return $this->dateTime <=> $comparisonDate;
    }

    /**
     * Check if date is in the past
     */
    public function isPast(?DateTimeInterface $relativeTo = null): bool
    {
        $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
        return $this->dateTime < $relativeTo;
    }

    /**
     * Check if date is in the future
     */
    public function isFuture(?DateTimeInterface $relativeTo = null): bool
    {
        $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
        return $this->dateTime > $relativeTo;
    }

    /**
     * Check if date is today
     */
    public function isToday(): bool
    {
        $now = new DateTime('now', $this->dateTime->getTimezone());
        return $this->dateTime->format('Y-m-d') === $now->format('Y-m-d');
    }

    /**
     * Get difference as DateInterval
     */
    public function diff($compareDate): DateInterval
    {
        $comparisonDate = $compareDate instanceof DateTimeInterface
            ? $compareDate
            : new DateTime($compareDate);

        return $this->dateTime->diff($comparisonDate);
    }

    /**
     * Get start of day (00:00:00)
     */
    public function startOfDay(): self
    {
        $dateTime = clone $this->dateTime;
        $dateTime->setTime(0, 0, 0);
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Get end of day (23:59:59)
     */
    public function endOfDay(): self
    {
        $dateTime = clone $this->dateTime;
        $dateTime->setTime(23, 59, 59);
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Get the timezone
     */
    public function getTimezone(): DateTimeZone
    {
        return $this->dateTime->getTimezone();
    }

    /**
     * Get the underlying DateTime object
     */
    public function getDateTime(): DateTimeInterface
    {
        return $this->dateTime;
    }

    public function toFriendlyFormat(): string
    {
        if ($this->isToday()) {
            return 'Today, ' . $this->toCustomFormat('h:i A');
        }

        if ($this->isFuture()) {
            return $this->toCustomFormat('D, h:i A'); // e.g., "Mon, 10:45 AM"
        }

        return $this->toCustomFormat('M j, h:i A'); // e.g., "Jul 14, 10:45 AM"
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->dateTime->format('Y-m-d H:i:s');
    }
}