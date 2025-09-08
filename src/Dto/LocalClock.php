<?php

declare(strict_types=1);

namespace Xgc\Dto;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use DomainException;
use Throwable;
use Xgc\Exception\BaseException;

use const DATE_ATOM;

/**
 * @phpstan-type CtTimeFormat 'seconds'|'minutes'|'hours'|'days'|'weeks'|'months'|'years'
 * @phpstan-type CtWeekDay 'Mon'|'Tue'|'Wed'|'Thu'|'Fri'|'Sat'|'Sun'
 */
class LocalClock
{
    private static ?string $globalTimezone = null;

    /**
     * @internal
     */
    private readonly DateTimeImmutable $dateTime;

    private readonly string $timezone;

    private function __construct(string $timezone, ?DateTimeInterface $dateTime = null)
    {
        try {
            if ($dateTime instanceof DateTime) {
                $dateTime = DateTimeImmutable::createFromMutable($dateTime)->setTimezone(new DateTimeZone($timezone));
            } elseif ($dateTime instanceof DateTimeImmutable) {
                $dateTime = $dateTime->setTimezone(new DateTimeZone($timezone));
            } else {
                $dateTime = new DateTimeImmutable()->setTimezone(new DateTimeZone($timezone));
            }
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }

        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
    }

    public static function defaultTimezone(): ?string
    {
        return self::$globalTimezone;
    }

    public static function fromString(string $dateTime, ?string $timezone = null): self
    {
        try {
            $timezone = self::realTimezone($timezone);

            return new self($timezone, new DateTimeImmutable($dateTime, new DateTimeZone($timezone)));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public static function fromStringOrNull(?string $dateTime, ?string $timezone = null): ?self
    {
        if ($dateTime === null) {
            return null;
        }

        return self::fromString($dateTime, $timezone);
    }

    /**
     * @param CtTimeFormat $in
     */
    public static function max(self $a, self $b, string $in = 'seconds'): self
    {
        return $a->isAfter($b, $in) ? $a : $b;
    }

    /**
     * @param CtTimeFormat $in
     */
    public static function min(self $a, self $b, string $in = 'seconds'): self
    {
        return $a->isBefore($b, $in) ? $a : $b;
    }

    public static function now(?string $timezone = null): self
    {
        $timezone = self::realTimezone($timezone);

        return Clock::now()->toLocal($timezone);
    }

    /**
     * @param CtTimeFormat $in
     * @return LocalClock[]
     */
    public static function sequence(self $from, self $to, string $in = 'days', bool $includeLast = true): array
    {
        if ($from->timezone() !== $to->timezone()) {
            throw new BaseException('Timezones are not equal.');
        }

        try {
            $ret = [];

            $current = $from;
            while ($current->isBefore($to, $in)) {
                $ret[] = $current;
                $current = new self($from->timezone(), $current->dateTime->modify("+1 {$in}"));
            }

            if ($includeLast) {
                $ret[] = $to;
            }

            return $ret;
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public static function setDefaultTimezone(?string $timezone): void
    {
        self::$globalTimezone = $timezone;
    }

    private static function realTimezone(?string $timezone): string
    {
        $t = $timezone ?? self::$globalTimezone;
        if ($t === null) {
            throw new BaseException('Timezone is not set.');
        }

        return $t;
    }

    public function __toString(): string
    {
        return $this->dateTimeString();
    }

    /**
     * @param CtTimeFormat $in
     */
    public function add(int $amount, string $in = 'seconds'): self
    {
        try {
            if ($amount === 0) {
                return $this;
            }

            if ($amount > 0) {
                return new self($this->timezone(), $this->dateTime->modify("+ {$amount} {$in}"));
            }

            $amount = -$amount;

            return new self($this->timezone(), $this->dateTime->modify("- {$amount} {$in}"));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    /**
     * @param CtTimeFormat $in
     */
    public function compare(self $to, string $in = 'seconds'): int
    {
        if ($to->timezone() !== $this->timezone) {
            throw new BaseException('Timezones are not equal.');
        }

        return $this->timestamp($in) <=> $to->timestamp($in);
    }

    public function dateString(): string
    {
        return $this->dateTime->format('Y-m-d');
    }

    public function dateTimeString(): string
    {
        return $this->dateTime->format('Y-m-d\TH:i:s');
    }

    public function day(): string
    {
        return $this->dateTime->format('d');
    }

    /**
     * @param CtTimeFormat $in
     */
    public function diff(self $from, string $in = 'seconds'): int
    {
        $diff = $this->dateTime->diff($from->dateTime);

        $absDiff = match ($in) {
            'seconds' => $diff->s + ($diff->i * 60) + ($diff->h * 3600) + ((int) $diff->days * 86400),
            'minutes' => $diff->i + ($diff->h * 60) + ((int) $diff->days * 1440),
            'hours' => $diff->h + ((int) $diff->days * 24),
            'days' => (int) $diff->days,
            'weeks' => (int) ((int) $diff->days / 7),
            'months' => $diff->m + ($diff->y * 12),
            default => $diff->y
        };

        return $diff->invert === 1 ? $absDiff : -$absDiff;
    }

    public function hour(): string
    {
        return $this->dateTime->format('H');
    }

    /**
     * @param CtTimeFormat $in
     */
    public function isAfter(self $to, string $in = 'seconds'): bool
    {
        return $this->compare($to, $in) > 0;
    }

    /**
     * @param CtTimeFormat $in
     */
    public function isAfterOrEqual(self $to, string $in = 'seconds'): bool
    {
        return $this->compare($to, $in) >= 0;
    }

    /**
     * @param CtTimeFormat $in
     */
    public function isBefore(self $to, string $in = 'seconds'): bool
    {
        return $this->compare($to, $in) < 0;
    }

    /**
     * @param CtTimeFormat $in
     */
    public function isBeforeOrEqual(self $to, string $in = 'seconds'): bool
    {
        return $this->compare($to, $in) <= 0;
    }

    /**
     * @param CtTimeFormat $in
     */
    public function isEqual(self $to, string $in = 'seconds'): bool
    {
        return $this->compare($to, $in) === 0;
    }

    /**
     * @param CtTimeFormat $in
     */
    public function isNotEqual(self $to, string $in = 'seconds'): bool
    {
        return $this->compare($to, $in) !== 0;
    }

    /**
     * @param CtTimeFormat $in
     */
    public function isSimilar(self $clock, int $offset, string $in = 'seconds'): bool
    {
        $diff = abs($this->diff($clock, $in));

        return $diff <= $offset;
    }

    public function microDateTimeString(): string
    {
        return $this->dateTime->format('Y-m-d\TH:i:s.u');
    }

    public function minute(): string
    {
        return $this->dateTime->format('i');
    }

    public function modify(string $modifier): self
    {
        try {
            return new self($this->timezone, $this->dateTime->modify($modifier));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public function month(): string
    {
        return $this->dateTime->format('m');
    }

    public function setTime(?int $hours = null, ?int $minutes = null, ?int $seconds = null): self
    {
        try {
            $currentHours = (int) $this->hour();
            $currentMinutes = (int) $this->minute();
            $currentSeconds = (int) $this->dateTime->format('s');

            return new self($this->timezone, $this->dateTime->setTime(
                $hours ?? $currentHours,
                $minutes ?? $currentMinutes,
                $seconds ?? $currentSeconds,
            ));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public function setTimeString(string $time): self
    {
        $parts = explode(':', $time);
        $hours = (int) $parts[0];
        $minutes = (int) ($parts[1] ?? '0');
        $seconds = (int) ($parts[2] ?? '0');

        return $this->setTime($hours, $minutes, $seconds);
    }

    public function setTimestamp(int $timestamp): self
    {
        return new self($this->timezone(), $this->dateTime->setTimestamp($timestamp));
    }

    public function setTimezone(string $timezone): self
    {
        return new self($timezone, $this->dateTime);
    }

    /**
     * @param CtWeekDay $weekDay
     */
    public function setWeekDayString(string $weekDay): self
    {
        $days = [
            'Mon' => 'monday',
            'Tue' => 'tuesday',
            'Wed' => 'wednesday',
            'Thu' => 'thursday',
            'Fri' => 'friday',
            'Sat' => 'saturday',
            'Sun' => 'sunday',
        ];

        try {
            return new self($this->timezone(), $this->dateTime->modify("{$days[$weekDay]} this week"));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public function setYearAndWeek(int $year, int $week): self
    {
        return new self($this->timezone(), $this->dateTime->setISODate($year, $week));
    }

    public function sinceString(): string
    {
        $since = $this->dateTime->getTimestamp() - time();

        $chunks = [
            [60 * 60 * 24 * 365, 'year'],
            [60 * 60 * 24 * 30, 'month'],
            [60 * 60 * 24 * 7, 'week'],
            [60 * 60 * 24, 'day'],
            [60 * 60, 'hour'],
            [60, 'minute'],
            [1, 'second'],
        ];

        $count = 0;
        $name = '';

        foreach ($chunks as $iValue) {
            [$seconds, $name] = $iValue;
            $count = (int) ($since / $seconds);
            if ($count !== 0) {
                break;
            }
        }

        if ($count === 0) {
            return 'just now';
        }

        if ($count === 1) {
            return "1 {$name} ago";
        }

        return "$count {$name}s ago";
    }

    public function timeString(bool $withSeconds = false): string
    {
        if ($withSeconds) {
            return $this->dateTime->format('H:i:s');
        }

        return $this->dateTime->format('H:i');
    }

    /**
     * @param CtTimeFormat $in
     */
    public function timestamp(string $in = 'seconds'): int
    {
        $ts = $this->dateTime->getTimestamp();

        return match ($in) {
            'seconds' => $ts,
            'minutes' => (int) ($ts / 60),
            'hours' => (int) ($ts / 3600),
            'days' => (int) ($ts / 86400),
            'weeks' => (int) ($ts / 604800),
            default => throw new DomainException("Invalid time format: {$in}."),
        };
    }

    public function timezone(): string
    {
        return $this->timezone;
    }

    public function toUTC(): Clock
    {
        return Clock::fromString($this->dateTime->setTimezone(new DateTimeZone('UTC'))->format(DATE_ATOM));
    }

    public function week(): string
    {
        return $this->dateTime->format('W');
    }

    public function weekDay(): int
    {
        return (int) $this->dateTime->format('w');
    }

    /**
     * @return CtWeekDay
     */
    public function weekDayString(): string
    {
        /** @var CtWeekDay $weekDay */
        $weekDay = $this->dateTime->format('D');

        return $weekDay;
    }

    public function year(): string
    {
        return $this->dateTime->format('Y');
    }

    public function yearAndWeek(): string
    {
        return $this->dateTime->format('oW');
    }
}
