<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Random\RandomException;
use Xgc\Exception\BaseException;

use function sprintf;

use const PHP_FLOAT_MAX;
use const PHP_FLOAT_MIN;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

class RandomUtil
{
    public static function float(float $min = PHP_FLOAT_MIN, float $max = PHP_FLOAT_MAX): float
    {
        return $min + (self::int() / PHP_INT_MAX) * ($max - $min);
    }

    public static function int(int $from = PHP_INT_MIN, int $to = PHP_INT_MAX): int
    {
        try {
            return random_int($from, $to);
        } catch (RandomException $e) {
            throw BaseException::extend($e);
        }
    }

    public static function uuid4(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            self::int(0, 0xFFFF),
            self::int(0, 0xFFFF),
            self::int(0, 0xFFFF),
            self::int(0, 0x0FFF) | 0x4000,
            self::int(0, 0x3FFF) | 0x8000,
            self::int(0, 0xFFFF),
            self::int(0, 0xFFFF),
            self::int(0, 0xFFFF),
        );
    }
}
