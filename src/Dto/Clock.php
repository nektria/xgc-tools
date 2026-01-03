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

/**
 * @phpstan-type CtTimeFormat 'seconds'|'minutes'|'hours'|'days'|'weeks'|'months'|'years'
 */
class Clock
{
    private DateTimeImmutable $dateTime;

    private function __construct(?DateTimeInterface $dateTime = null)
    {
        if ($dateTime instanceof DateTime) {
            $dateTime = DateTimeImmutable::createFromMutable($dateTime);
        }
        $this->dateTime = $dateTime ?? new DateTimeImmutable();
    }

    public static function fromString(string $dateTime): self
    {
        try {
            return new self(new DateTimeImmutable($dateTime)->setTimezone(new DateTimeZone('UTC')));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public static function fromStringOrNull(?string $dateTime): ?self
    {
        if ($dateTime === null) {
            return null;
        }

        try {
            return new self(new DateTimeImmutable($dateTime)->setTimezone(new DateTimeZone('UTC')));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public static function fromTimestamp(int $timestamp): self
    {
        try {
            return new self(new DateTimeImmutable("@{$timestamp}"));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
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

    public static function now(): self
    {
        return new self();
    }

    /**
     * @param CtTimeFormat $in
     * @return Clock[]
     */
    public static function sequence(self $from, self $to, string $in = 'days', bool $includeLast = true): array
    {
        try {
            $ret = [];

            $current = $from;
            while ($current->isBefore($to, $in)) {
                $ret[] = $current;
                $current = new self($current->dateTime->modify("+1 {$in}"));
            }

            if ($includeLast) {
                $ret[] = $to;
            }

            return $ret;
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public function __toString(): string
    {
        try {
            return $this->microDateTimeString();
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    /**
     * @param CtTimeFormat $in
     */
    public function add(int $amount, string $in = 'seconds'): self
    {
        return $this->modify("+ {$amount} {$in}");
    }

    /**
     * @param CtTimeFormat $in
     */
    public function compare(self $to, string $in = 'seconds'): int
    {
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

    public function iso8601String(): string
    {
        return $this->dateTime->format(DateTimeInterface::ATOM);
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
            return new self($this->dateTime->modify($modifier));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public function month(): string
    {
        return $this->dateTime->format('m');
    }

    public function native(): DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function rfc1123String(): string
    {
        return $this->dateTime->format('D, d M Y H:i:s') . ' GMT';
    }

    public function setTime(?int $hours = null, ?int $minutes = null, ?int $seconds = null): self
    {
        try {
            $currentHours = (int) $this->hour();
            $currentMinutes = (int) $this->minute();
            $currentSeconds = (int) $this->dateTime->format('s');

            return new self($this->dateTime->setTime(
                $hours ?? $currentHours,
                $minutes ?? $currentMinutes,
                $seconds ?? $currentSeconds,
            ));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public function setYearAndWeek(int $year, int $week): self
    {
        return new self($this->dateTime->setISODate($year, $week));
    }

    public function timeString(): string
    {
        return $this->dateTime->format('H:i:s');
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

    public function toLocal(?string $timezone = null): LocalClock
    {
        $timezone ??= LocalClock::defaultTimezone();

        if ($timezone === null) {
            throw new BaseException('Timezone is not set.');
        }

        try {
            $dateTimeString = $this->dateTime->setTimezone(new DateTimeZone($timezone))->format('Y-m-d\TH:i:s');
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }

        return LocalClock::fromString($dateTimeString, $timezone);
    }

    public function week(): string
    {
        return $this->dateTime->format('W');
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
