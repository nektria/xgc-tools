<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Random\Randomizer;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Throwable;

use const STR_PAD_LEFT;

class StringUtil
{
    public const string LOWER_CASE = 'abcdefghijklmnopqrstuvwxyz';

    public const string NUMBERS = '0123456789';

    public const string SYMBOLS = '!@#$%^&*()_+{}|:<>?';

    public const string UPPER_CASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function bytes(
        int $length,
        bool $lowerCase = true,
        bool $upperCase = true,
        bool $numbers = true,
        bool $symbols = false,
    ): string {
        $randomizer = new Randomizer();
        $chars = '';
        if ($lowerCase) {
            $chars .= self::LOWER_CASE;
        }
        if ($upperCase) {
            $chars .= self::UPPER_CASE;
        }
        if ($numbers) {
            $chars .= self::NUMBERS;
        }
        if ($symbols) {
            $chars .= self::SYMBOLS;
        }

        return $randomizer->getBytesFromString(
            $chars,
            $length,
        );
    }

    public static function capitalize(string $input): string
    {
        return ucwords(strtolower($input));
    }

    public static function className(object $class): string
    {
        $path = explode('\\', $class::class);

        return array_pop($path);
    }

    public static function fit(string $value, int $length): string
    {
        $value .= '00000000000000000000000000000000';

        return substr($value, 0, $length);
    }

    public static function randomColor(): string
    {
        try {
            return '#' . str_pad(dechex(random_int(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        } catch (Throwable) {
            return '#000000';
        }
    }

    public static function slug(string $input, bool $trimFirst = true): string
    {
        if ($trimFirst) {
            $input = self::trim($input);
        }

        $slugger = new AsciiSlugger();

        return strtolower($slugger->slug($input)->toString());
    }

    public static function trim(string $input): string
    {
        $result = preg_replace('/\s+/', ' ', trim($input)) ?? '';

        if ($result === ' ') {
            return '';
        }

        return $result;
    }

    public static function uuid4(): string
    {
        return RandomUtil::uuid4();
    }
}
