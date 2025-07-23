<?php

declare(strict_types=1);

namespace Xgc\Cache;

use Xgc\Dto\Clock;
use Xgc\Dto\ContextInterface;

/**
 * @extends InternalRedisCache<string|bool|int|float>
 */
class InternalVariableCache extends InternalRedisCache
{
    public const string DEFAULT = '1';

    public const int DEFAULT_TTL = 300;

    public function __construct(
        string $redisDsn,
        ContextInterface $context,
    ) {
        $redisDsn = str_replace('/0', '/2', $redisDsn);
        parent::__construct($redisDsn, $context);
    }

    public function deleteKey(string $key): void
    {
        $this->removeItem($key);
    }

    public function executeIfNotExists(string $key, callable $callback, int $ttl = self::DEFAULT_TTL): void
    {
        if ($this->hasKey($key)) {
            return;
        }

        $this->saveKey($key, $ttl);
        $callback();
    }

    public function hasKey(string $key): bool
    {
        $value = $this->getItem($key);

        return $value !== null;
    }

    public function readClock(string $key): ?Clock
    {
        $clock = $this->readString($key);

        if ($clock === null) {
            return null;
        }

        return Clock::fromString($clock);
    }

    public function readInt(string $key, int $default = 0): int
    {
        return (int) ($this->getItem($key) ?? $default);
    }

    /**
     * @param string[] $keys
     * @return array<string, int>
     */
    public function readMultipleInt(array $keys): array
    {
        $values = $this->getItems($keys);
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = (int) ($values[$key] ?? 0);
        }

        return $result;
    }

    public function readString(string $key): ?string
    {
        $value = $this->getItem($key);

        if ($value === null) {
            return null;
        }

        return (string) $value;
    }

    public function refreshKey(string $key, int $ttl = self::DEFAULT_TTL): bool
    {
        $isNew = !$this->hasKey($key);
        $value = $this->getItem($key) ?? self::DEFAULT;
        if ($isNew) {
            $this->setItem($key, $value, $ttl);
        }

        return $isNew;
    }

    public function saveClock(string $key, Clock $value, int $ttl = self::DEFAULT_TTL): void
    {
        $this->setItem($key, $value->iso8601String(), $ttl);
    }

    public function saveInt(string $key, int $value, int $ttl = self::DEFAULT_TTL): void
    {
        $this->setItem($key, $value, $ttl);
    }

    public function saveKey(string $key, int $ttl = self::DEFAULT_TTL): void
    {
        $this->setItem($key, self::DEFAULT, $ttl);
    }

    public function saveString(string $key, string $value, int $ttl = self::DEFAULT_TTL): void
    {
        $this->setItem($key, $value, $ttl);
    }
}
