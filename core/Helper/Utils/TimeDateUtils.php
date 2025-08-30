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
        
        try {
            $timeZone = $timeZone instanceof DateTimeZone ? $timeZone : new DateTimeZone($timeZone);
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid timezone: " . $e->getMessage());
        }

        try {
            if ($dateTime instanceof DateTimeInterface) {
                if ($immutable) {
                    $this->dateTime = $dateTime instanceof DateTimeImmutable 
                        ? $dateTime->setTimezone($timeZone)
                        : DateTimeImmutable::createFromInterface($dateTime)->setTimezone($timeZone);
                } else {
                    $this->dateTime = new DateTime('@' . $dateTime->getTimestamp());
                    $this->dateTime = $this->dateTime->setTimezone($timeZone);
                }
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
        try {
            $timeZoneObj = $timeZone instanceof DateTimeZone ? $timeZone : new DateTimeZone($timeZone);
            
            if ($immutable) {
                $dateTime = new DateTimeImmutable('@' . $timestamp);
                $dateTime = $dateTime->setTimezone($timeZoneObj);
            } else {
                $dateTime = new DateTime('@' . $timestamp);
                $dateTime->setTimezone($timeZoneObj);
            }

            return new static($dateTime, $timeZoneObj, $immutable);
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid timestamp or timezone: " . $e->getMessage());
        }
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
        try {
            $timeZoneObj = $timeZone instanceof DateTimeZone ? $timeZone : new DateTimeZone($timeZone);
            
            $dateTimeObj = $immutable
                ? DateTimeImmutable::createFromFormat($format, $dateTime, $timeZoneObj)
                : DateTime::createFromFormat($format, $dateTime, $timeZoneObj);

            if ($dateTimeObj === false) {
                throw new InvalidArgumentException(
                    "Could not parse date/time '$dateTime' from format '$format'"
                );
            }

            return new static($dateTimeObj, $timeZoneObj, $immutable);
        } catch (Exception $e) {
            throw new InvalidArgumentException(
                "Invalid format, date/time, or timezone: " . $e->getMessage()
            );
        }
    }

    /**
     * Create instance for now
     */
    public static function now($timeZone = 'UTC', bool $immutable = false): self
    {
        return new static('now', $timeZone, $immutable);
    }

    /**
     * Create instance for today at start of day
     */
    public static function today($timeZone = 'UTC', bool $immutable = false): self
    {
        return static::now($timeZone, $immutable)->startOfDay();
    }

    /**
     * Create instance for tomorrow at start of day
     */
    public static function tomorrow($timeZone = 'UTC', bool $immutable = false): self
    {
        return static::today($timeZone, $immutable)->addDays(1);
    }

    /**
     * Create instance for yesterday at start of day
     */
    public static function yesterday($timeZone = 'UTC', bool $immutable = false): self
    {
        return static::today($timeZone, $immutable)->subDays(1);
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

        if (!$diff->invert) {
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
        if ($this->immutable) {
            $dateTime = $this->dateTime->add($interval);
        } else {
            $dateTime = clone $this->dateTime;
            $dateTime->add($interval);
        }
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Subtract time interval
     */
    public function sub(DateInterval $interval): self
    {
        if ($this->immutable) {
            $dateTime = $this->dateTime->sub($interval);
        } else {
            $dateTime = clone $this->dateTime;
            $dateTime->sub($interval);
        }
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Add days
     */
    public function addDays(int $days): self
    {
        if ($days < 0) {
            return $this->subDays(abs($days));
        }
        return $this->add(new DateInterval("P{$days}D"));
    }

    /**
     * Subtract days
     */
    public function subDays(int $days): self
    {
        if ($days < 0) {
            return $this->addDays(abs($days));
        }
        return $this->sub(new DateInterval("P{$days}D"));
    }

    /**
     * Add hours
     */
    public function addHours(int $hours): self
    {
        if ($hours < 0) {
            return $this->subHours(abs($hours));
        }
        return $this->add(new DateInterval("PT{$hours}H"));
    }

    /**
     * Subtract hours
     */
    public function subHours(int $hours): self
    {
        if ($hours < 0) {
            return $this->addHours(abs($hours));
        }
        return $this->sub(new DateInterval("PT{$hours}H"));
    }

    /**
     * Add minutes
     */
    public function addMinutes(int $minutes): self
    {
        if ($minutes < 0) {
            return $this->subMinutes(abs($minutes));
        }
        return $this->add(new DateInterval("PT{$minutes}M"));
    }

    /**
     * Subtract minutes
     */
    public function subMinutes(int $minutes): self
    {
        if ($minutes < 0) {
            return $this->addMinutes(abs($minutes));
        }
        return $this->sub(new DateInterval("PT{$minutes}M"));
    }

    /**
     * Add seconds
     */
    public function addSeconds(int $seconds): self
    {
        if ($seconds < 0) {
            return $this->subSeconds(abs($seconds));
        }
        return $this->add(new DateInterval("PT{$seconds}S"));
    }

    /**
     * Subtract seconds
     */
    public function subSeconds(int $seconds): self
    {
        if ($seconds < 0) {
            return $this->addSeconds(abs($seconds));
        }
        return $this->sub(new DateInterval("PT{$seconds}S"));
    }

    /**
     * Add weeks
     */
    public function addWeeks(int $weeks): self
    {
        return $this->addDays($weeks * 7);
    }

    /**
     * Subtract weeks
     */
    public function subWeeks(int $weeks): self
    {
        return $this->subDays($weeks * 7);
    }

    /**
     * Add months
     */
    public function addMonths(int $months): self
    {
        if ($months < 0) {
            return $this->subMonths(abs($months));
        }
        return $this->add(new DateInterval("P{$months}M"));
    }

    /**
     * Subtract months
     */
    public function subMonths(int $months): self
    {
        if ($months < 0) {
            return $this->addMonths(abs($months));
        }
        return $this->sub(new DateInterval("P{$months}M"));
    }

    /**
     * Add years
     */
    public function addYears(int $years): self
    {
        if ($years < 0) {
            return $this->subYears(abs($years));
        }
        return $this->add(new DateInterval("P{$years}Y"));
    }

    /**
     * Subtract years
     */
    public function subYears(int $years): self
    {
        if ($years < 0) {
            return $this->addYears(abs($years));
        }
        return $this->sub(new DateInterval("P{$years}Y"));
    }

    /**
     * Set timezone
     */
    public function setTimezone($timeZone): self
    {
        try {
            $timeZoneObj = $timeZone instanceof DateTimeZone
                ? $timeZone
                : new DateTimeZone($timeZone);

            if ($this->immutable) {
                $dateTime = $this->dateTime->setTimezone($timeZoneObj);
            } else {
                $dateTime = clone $this->dateTime;
                $dateTime->setTimezone($timeZoneObj);
            }
            
            return new static($dateTime, $timeZoneObj, $this->immutable);
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid timezone: " . $e->getMessage());
        }
    }

    /**
     * Compare with another date/time
     */
    public function compareTo($compareDate): int
    {
        try {
            $comparisonDate = $compareDate instanceof DateTimeInterface
                ? $compareDate
                : new DateTime($compareDate);

            return $this->dateTime <=> $comparisonDate;
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid comparison date: " . $e->getMessage());
        }
    }

    /**
     * Check if date is equal to another date
     */
    public function equals($compareDate): bool
    {
        return $this->compareTo($compareDate) === 0;
    }

    /**
     * Check if date is before another date
     */
    public function isBefore($compareDate): bool
    {
        return $this->compareTo($compareDate) < 0;
    }

    /**
     * Check if date is after another date
     */
    public function isAfter($compareDate): bool
    {
        return $this->compareTo($compareDate) > 0;
    }

    /**
     * Check if date is in the past
     */
    public function isPast(?DateTimeInterface $relativeTo = null): bool
    {
        try {
            $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
            return $this->dateTime < $relativeTo;
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid relative date: " . $e->getMessage());
        }
    }

    /**
     * Check if date is in the future
     */
    public function isFuture(?DateTimeInterface $relativeTo = null): bool
    {
        try {
            $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
            return $this->dateTime > $relativeTo;
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid relative date: " . $e->getMessage());
        }
    }

    /**
     * Check if date is today
     */
    public function isToday(?DateTimeInterface $relativeTo = null): bool
    {
        try {
            $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
            return $this->dateTime->format('Y-m-d') === $relativeTo->format('Y-m-d');
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid relative date: " . $e->getMessage());
        }
    }

    /**
     * Check if date is yesterday
     */
    public function isYesterday(?DateTimeInterface $relativeTo = null): bool
    {
        try {
            $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
            $yesterday = (clone $relativeTo)->sub(new DateInterval('P1D'));
            return $this->dateTime->format('Y-m-d') === $yesterday->format('Y-m-d');
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid relative date: " . $e->getMessage());
        }
    }

    /**
     * Check if date is tomorrow
     */
    public function isTomorrow(?DateTimeInterface $relativeTo = null): bool
    {
        try {
            $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
            $tomorrow = (clone $relativeTo)->add(new DateInterval('P1D'));
            return $this->dateTime->format('Y-m-d') === $tomorrow->format('Y-m-d');
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid relative date: " . $e->getMessage());
        }
    }

    /**
     * Check if date is a weekend (Saturday or Sunday)
     */
    public function isWeekend(): bool
    {
        $dayOfWeek = (int) $this->dateTime->format('w');
        return $dayOfWeek === 0 || $dayOfWeek === 6; // Sunday = 0, Saturday = 6
    }

    /**
     * Check if date is a weekday (Monday through Friday)
     */
    public function isWeekday(): bool
    {
        return !$this->isWeekend();
    }

    /**
     * Get difference as DateInterval
     */
    public function diff($compareDate, bool $absolute = false): DateInterval
    {
        try {
            $comparisonDate = $compareDate instanceof DateTimeInterface
                ? $compareDate
                : new DateTime($compareDate);

            return $this->dateTime->diff($comparisonDate, $absolute);
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid comparison date: " . $e->getMessage());
        }
    }

    /**
     * Get difference in days
     */
    public function diffInDays($compareDate, bool $absolute = false): int
    {
        $diff = $this->diff($compareDate, $absolute);
        return (int) $diff->format('%r%a');
    }

    /**
     * Get difference in hours
     */
    public function diffInHours($compareDate, bool $absolute = false): int
    {
        $diff = $this->diff($compareDate, $absolute);
        return (int) $diff->format('%r') * (
            ($diff->y * 365 * 24) + 
            ($diff->m * 30 * 24) + 
            ($diff->d * 24) + 
            $diff->h
        );
    }

    /**
     * Get difference in minutes
     */
    public function diffInMinutes($compareDate, bool $absolute = false): int
    {
        return $this->diffInHours($compareDate, $absolute) * 60 + 
               (int) $this->diff($compareDate, $absolute)->format('%i');
    }

    /**
     * Get difference in seconds
     */
    public function diffInSeconds($compareDate, bool $absolute = false): int
    {
        try {
            $comparisonDate = $compareDate instanceof DateTimeInterface
                ? $compareDate
                : new DateTime($compareDate);

            $diff = $this->dateTime->getTimestamp() - $comparisonDate->getTimestamp();
            return $absolute ? abs($diff) : $diff;
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid comparison date: " . $e->getMessage());
        }
    }

    /**
     * Get start of day (00:00:00)
     */
    public function startOfDay(): self
    {
        if ($this->immutable) {
            $dateTime = $this->dateTime->setTime(0, 0, 0);
        } else {
            $dateTime = clone $this->dateTime;
            $dateTime->setTime(0, 0, 0);
        }
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Get end of day (23:59:59)
     */
    public function endOfDay(): self
    {
        if ($this->immutable) {
            $dateTime = $this->dateTime->setTime(23, 59, 59, 999999);
        } else {
            $dateTime = clone $this->dateTime;
            $dateTime->setTime(23, 59, 59, 999999);
        }
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Get start of week (Monday 00:00:00)
     */
    public function startOfWeek(): self
    {
        $dayOfWeek = (int) $this->dateTime->format('w');
        $daysToSubtract = $dayOfWeek === 0 ? 6 : $dayOfWeek - 1; // Handle Sunday as 0
        
        return $this->subDays($daysToSubtract)->startOfDay();
    }

    /**
     * Get end of week (Sunday 23:59:59)
     */
    public function endOfWeek(): self
    {
        $dayOfWeek = (int) $this->dateTime->format('w');
        $daysToAdd = $dayOfWeek === 0 ? 0 : 7 - $dayOfWeek;
        
        return $this->addDays($daysToAdd)->endOfDay();
    }

    /**
     * Get start of month
     */
    public function startOfMonth(): self
    {
        if ($this->immutable) {
            $dateTime = $this->dateTime->setDate(
                (int) $this->dateTime->format('Y'),
                (int) $this->dateTime->format('m'),
                1
            )->setTime(0, 0, 0);
        } else {
            $dateTime = clone $this->dateTime;
            $dateTime->setDate(
                (int) $this->dateTime->format('Y'),
                (int) $this->dateTime->format('m'),
                1
            );
            $dateTime->setTime(0, 0, 0);
        }
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Get end of month
     */
    public function endOfMonth(): self
    {
        $lastDay = (int) $this->dateTime->format('t');
        
        if ($this->immutable) {
            $dateTime = $this->dateTime->setDate(
                (int) $this->dateTime->format('Y'),
                (int) $this->dateTime->format('m'),
                $lastDay
            )->setTime(23, 59, 59, 999999);
        } else {
            $dateTime = clone $this->dateTime;
            $dateTime->setDate(
                (int) $this->dateTime->format('Y'),
                (int) $this->dateTime->format('m'),
                $lastDay
            );
            $dateTime->setTime(23, 59, 59, 999999);
        }
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Get start of year
     */
    public function startOfYear(): self
    {
        if ($this->immutable) {
            $dateTime = $this->dateTime->setDate(
                (int) $this->dateTime->format('Y'),
                1,
                1
            )->setTime(0, 0, 0);
        } else {
            $dateTime = clone $this->dateTime;
            $dateTime->setDate((int) $this->dateTime->format('Y'), 1, 1);
            $dateTime->setTime(0, 0, 0);
        }
        return new static($dateTime, $dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Get end of year
     */
    public function endOfYear(): self
    {
        if ($this->immutable) {
            $dateTime = $this->dateTime->setDate(
                (int) $this->dateTime->format('Y'),
                12,
                31
            )->setTime(23, 59, 59, 999999);
        } else {
            $dateTime = clone $this->dateTime;
            $dateTime->setDate((int) $this->dateTime->format('Y'), 12, 31);
            $dateTime->setTime(23, 59, 59, 999999);
        }
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

    /**
     * Format date for friendly display
     */
    public function toFriendlyFormat(?DateTimeInterface $relativeTo = null): string
    {
        try {
            $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
            
            if ($this->isToday($relativeTo)) {
                return 'Today, ' . $this->toCustomFormat('h:i A');
            }

            if ($this->isYesterday($relativeTo)) {
                return 'Yesterday, ' . $this->toCustomFormat('h:i A');
            }

            if ($this->isTomorrow($relativeTo)) {
                return 'Tomorrow, ' . $this->toCustomFormat('h:i A');
            }

            // Check if within the current week
            $daysDiff = abs($this->diffInDays($relativeTo));
            if ($daysDiff <= 7) {
                return $this->toCustomFormat('l, h:i A'); // e.g., "Monday, 10:45 AM"
            }

            // Check if within current year
            if ($this->dateTime->format('Y') === $relativeTo->format('Y')) {
                return $this->toCustomFormat('M j, h:i A'); // e.g., "Jul 14, 10:45 AM"
            }

            return $this->toCustomFormat('M j, Y h:i A'); // e.g., "Jul 14, 2023 10:45 AM"
        } catch (Exception $e) {
            // Fallback to standard format if any error occurs
            return $this->toCustomFormat('M j, Y h:i A');
        }
    }

    /**
     * Get age in years from the current date
     */
    public function getAge(?DateTimeInterface $relativeTo = null): int
    {
        try {
            $relativeTo = $relativeTo ?? new DateTime('now', $this->dateTime->getTimezone());
            $diff = $this->dateTime->diff($relativeTo);
            return $diff->invert ? 0 : $diff->y;
        } catch (Exception $e) {
            throw new InvalidArgumentException("Invalid relative date: " . $e->getMessage());
        }
    }

    /**
     * Check if this year is a leap year
     */
    public function isLeapYear(): bool
    {
        $year = (int) $this->dateTime->format('Y');
        return ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
    }

    /**
     * Get quarter of the year (1-4)
     */
    public function getQuarter(): int
    {
        $month = (int) $this->dateTime->format('n');
        return (int) ceil($month / 3);
    }

    /**
     * Get day of year (1-366)
     */
    public function getDayOfYear(): int
    {
        return (int) $this->dateTime->format('z') + 1;
    }

    /**
     * Get week of year (1-53)
     */
    public function getWeekOfYear(): int
    {
        return (int) $this->dateTime->format('W');
    }

    /**
     * Create a copy of this instance
     */
    public function copy(): self
    {
        return new static($this->dateTime, $this->dateTime->getTimezone(), $this->immutable);
    }

    /**
     * Get array representation
     */
    public function toArray(): array
    {
        return [
            'date' => $this->dateTime->format('Y-m-d'),
            'time' => $this->dateTime->format('H:i:s'),
            'datetime' => $this->dateTime->format('Y-m-d H:i:s'),
            'timestamp' => $this->dateTime->getTimestamp(),
            'timezone' => $this->dateTime->getTimezone()->getName(),
            'iso8601' => $this->toISO8601(),
            'rfc2822' => $this->toRFC2822(),
        ];
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return $this->dateTime->format('Y-m-d H:i:s');
    }

    /**
     * JSON serialization
     */
    public function jsonSerialize(): string
    {
        return $this->toISO8601();
    }

    /**
     * Debug information
     */
    public function __debugInfo(): array
    {
        return [
            'datetime' => $this->dateTime->format('Y-m-d H:i:s'),
            'timezone' => $this->dateTime->getTimezone()->getName(),
            'timestamp' => $this->dateTime->getTimestamp(),
            'immutable' => $this->immutable,
        ];
    }
}