<?php

declare(strict_types=1);

namespace Xgc\Utils;

use IteratorAggregate;

use function count;
use function in_array;
use function is_string;

readonly class ArrayUtil
{
    /**
     * @template T
     * @param T[] $list
     * @param T $item
     * @return T[]
     */
    public static function addUnique(array $list, mixed $item): array
    {
        if (in_array($item, $list, true)) {
            return $list;
        }

        $list[] = $item;

        return self::unique($list);
    }

    /**
     * @template T
     * @param array<int, T>|IteratorAggregate<int, T> $list
     * @param callable(T): string $callback
     * @return array<string, T[]>
     */
    public static function classify(array | IteratorAggregate $list, callable $callback): array
    {
        $result = [];
        foreach ($list as $item) {
            $key = $callback($item);
            $result[$key] ??= [];
            $result[$key][] = $item;
        }

        return $result;
    }

    /**
     * @template T
     * @param T[] $array1
     * @param T[] $array2
     * @return T[]
     */
    public static function commonItems(array $array1, array $array2): array
    {
        return array_intersect($array1, $array2);
    }

    /**
     * @template T
     * @param T[] $new
     * @param T[] $old
     * @return array{
     *     added: T[],
     *     removed: T[],
     * }
     */
    public static function diff(array $new, array $old): array
    {
        return [
            'added' => array_diff($new, $old),
            'removed' => array_diff($old, $new),
        ];
    }

    /**
     * @template T
     * @param T[] $smallOne
     * @param T[] $bigOne
     */
    public static function isSubset(array $smallOne, array $bigOne): bool
    {
        return count(array_diff($smallOne, $bigOne)) === 0;
    }

    /**
     * @template T
     * @param array<int,T>|IteratorAggregate<int, T> $list
     * @param callable(T): string $callback
     * @return array<string, T>
     */
    public static function mapify(array | IteratorAggregate $list, callable $callback, bool $keepFirst = false): array
    {
        $result = [];
        foreach ($list as $item) {
            $key = $callback($item);
            if (isset($result[$key]) && !$keepFirst) {
                continue;
            }

            $result[$key] = $item;
        }

        return $result;
    }

    /**
     * @template T of array
     * @param array<int, T> $list
     * @return array<string, T>
     */
    public static function mapifyArray(array $list, string $field): array
    {
        return self::mapify(
            $list,
            static fn (array $item): string => is_string($item[$field]) ? $item[$field] : 'null'
        );
    }

    /**
     * @template T
     * @param T[] $list
     * @param T $item
     * @return T[]
     */
    public static function remove(array $list, mixed $item): array
    {
        $key = array_search($item, $list, true);
        if ($key === false) {
            return $list;
        }

        unset($list[$key]);

        return array_values($list);
    }

    /**
     * @template T
     * @param T[] $array1
     * @param T[] $array2
     */
    public static function sameItems(array $array1, array $array2): bool
    {
        $diffs = self::diff($array1, $array2);

        return count($diffs['added']) === 0 && count($diffs['removed']) === 0;
    }

    /**
     * @template T
     * @param T[] $list
     * @return T[]
     */
    public static function unique(array $list): array
    {
        return array_values(array_unique($list));
    }
}
