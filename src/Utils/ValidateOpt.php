<?php

declare(strict_types=1);

namespace Xgc\Utils;

readonly class ValidateOpt
{
    // dates

    public static function color(string $field, ?string $value): void
    {
        if ($value !== null) {
            Validate::color($field, $value);
        }
    }

    // numbers

    public static function date(string $field, ?string $date): void
    {
        if ($date !== null) {
            Validate::date($field, $date);
        }
    }

    public static function email(string $field, ?string $value): void
    {
        if ($value !== null) {
            Validate::email($field, $value);
        }
    }

    public static function greaterOrEqualThan(string $field, int | float | null $number, int | float $limit): void
    {
        if ($number !== null) {
            Validate::greaterOrEqualThan($field, $number, $limit);
        }
    }

    public static function greaterThan(string $field, int | float | null $number, int | float $limit): void
    {
        if ($number !== null) {
            Validate::greaterThan($field, $number, $limit);
        }
    }

    // Strings

    /**
     * @param string[] $validValues
     */
    public static function inStringList(string $field, ?string $value, array $validValues): void
    {
        if ($value !== null) {
            Validate::inStringList($field, $value, $validValues);
        }
    }

    public static function latitude(string $field, ?float $value): void
    {
        if ($value !== null) {
            Validate::latitude($field, $value);
        }
    }

    public static function lessThan(string $field, int | float | null $number, int | float $limit): void
    {
        if ($number !== null) {
            Validate::lessThan($field, $number, $limit);
        }
    }

    public static function longitude(string $field, ?float $value): void
    {
        if ($value !== null) {
            Validate::longitude($field, $value);
        }
    }

    public static function maxStringLength(string $field, ?string $value, int $length): void
    {
        if ($value !== null) {
            Validate::maxStringLength($field, $value, $length);
        }
    }

    /**
     * @param mixed[]|null $list
     */
    public static function minArrayLength(string $field, ?array $list, int $length): void
    {
        if ($list !== null) {
            Validate::minArrayLength($field, $list, $length);
        }
    }

    public static function minStringLength(string $field, ?string $value, int $length): void
    {
        if ($value !== null) {
            Validate::minStringLength($field, $value, $length);
        }
    }

    // coordinates

    public static function naturalNumber(string $field, int | float | null $number): void
    {
        if ($number !== null) {
            Validate::naturalNumber($field, $number);
        }
    }

    public static function notEmpty(string $field, ?string $value): void
    {
        if ($value !== null) {
            Validate::notEmpty($field, $value);
        }
    }

    public static function percentileNumber(string $field, int | float | null $number): void
    {
        if ($number !== null) {
            Validate::percentileNumber($field, $number);
        }
    }

    public static function regexp(string $field, ?string $value, string $regexp): void
    {
        if ($value !== null) {
            Validate::regexp($field, $value, $regexp);
        }
    }

    public static function timezone(string $field, ?string $value): void
    {
        if ($value !== null) {
            Validate::timezone($field, $value);
        }
    }

    public static function uuid4(string $field, ?string $value): void
    {
        if ($value !== null) {
            Validate::uuid4($field, $value);
        }
    }
}
