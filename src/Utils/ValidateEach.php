<?php

declare(strict_types=1);

namespace Xgc\Utils;

class ValidateEach
{
    // dates

    /**
     * @param string[] $values
     */
    public static function color(string $field, array $values): void
    {
        foreach ($values as $i => $item) {
            Validate::color("{$field}.{$i}", $item);
        }
    }

    // numbers

    /**
     * @param string[] $values
     */
    public static function date(string $field, array $values): void
    {
        foreach ($values as $i => $item) {
            Validate::date("{$field}.{$i}", $item);
        }
    }

    /**
     * @param string[] $values
     */
    public static function email(string $field, array $values): void
    {
        foreach ($values as $i => $item) {
            Validate::email("{$field}.{$i}", $item);
        }
    }

    /**
     * @param int[]|float[]|string[] $values
     */
    public static function greaterOrEqualThan(string $field, array $values, int | float | string $limit): void
    {
        foreach ($values as $i => $item) {
            Validate::greaterOrEqualThan("{$field}.{$i}", $item, $limit);
        }
    }

    /**
     * @param int[]|float[]|string[] $values
     */
    public static function greaterThan(string $field, array $values, int | float | string $limit): void
    {
        foreach ($values as $i => $item) {
            Validate::greaterThan("{$field}.{$i}", $item, $limit);
        }
    }

    /**
     * @param string[] $values
     * @param string[] $validValues
     */
    public static function inStringList(string $field, array $values, array $validValues): void
    {
        foreach ($values as $i => $item) {
            Validate::inStringList("{$field}.{$i}", $item, $validValues);
        }
    }

    // Strings

    /**
     * @param float[] $values
     */
    public static function latitude(string $field, array $values): void
    {
        foreach ($values as $i => $item) {
            Validate::latitude("{$field}.{$i}", $item);
        }
    }

    /**
     * @param int[]|float[] $values
     */
    public static function lessThan(string $field, array $values, int | float $limit): void
    {
        foreach ($values as $i => $item) {
            Validate::lessThan("{$field}.{$i}", $item, $limit);
        }
    }

    /**
     * @param float[] $values
     */
    public static function longitude(string $field, array $values): void
    {
        foreach ($values as $i => $item) {
            Validate::longitude("{$field}.{$i}", $item);
        }
    }

    /**
     * @param string[] $values
     */
    public static function maxStringLength(string $field, array $values, int $length): void
    {
        foreach ($values as $i => $item) {
            Validate::maxStringLength("{$field}.{$i}", $item, $length);
        }
    }

    /**
     * @param mixed[][] $values
     */
    public static function minArrayLength(string $field, array $values, int $length): void
    {
        foreach ($values as $i => $item) {
            Validate::minArrayLength("{$field}.{$i}", $item, $length);
        }
    }

    /**
     * @param string[] $values
     */
    public static function minStringLength(string $field, array $values, int $length): void
    {
        foreach ($values as $i => $item) {
            Validate::minStringLength("{$field}.{$i}", $item, $length);
        }
    }

    /**
     * @param int[]|float[] $values
     */
    public static function naturalNumber(string $field, array $values): void
    {
        foreach ($values as $i => $item) {
            Validate::naturalNumber("{$field}.{$i}", $item);
        }
    }

    // coordinates

    /**
     * @param string[] $values
     */
    public static function notEmpty(string $field, array $values): void
    {
        foreach ($values as $i => $item) {
            Validate::notEmpty("{$field}.{$i}", $item);
        }
    }

    /**
     * @param int[]|float[] $values
     */
    public static function percentileNumber(string $field, array $values): void
    {
        foreach ($values as $i => $item) {
            Validate::percentileNumber("{$field}.{$i}", $item);
        }
    }

    /**
     * @param string[] $values
     */
    public static function regexp(string $field, array $values, string $regexp): void
    {
        foreach ($values as $i => $item) {
            Validate::regexp("{$field}.{$i}", $item, $regexp);
        }
    }

    // times

    /**
     * @param string[] $values
     */
    public static function timezone(string $field, array $values): void
    {
        foreach ($values as $i => $item) {
            Validate::timezone("{$field}.{$i}", $item);
        }
    }

    // array

    /**
     * @param string[] $values
     */
    public static function uuid4(string $field, array $values): void
    {
        foreach ($values as $i => $item) {
            Validate::uuid4("{$field}.{$i}", $item);
        }
    }
}
