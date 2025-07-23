<?php

declare(strict_types=1);

namespace Xgc\Cache;

use Xgc\Dto\ContextInterface;

/**
 * @extends SharedRedisCache<string|bool|int|float>
 */
class SharedVariableCache extends SharedRedisCache
{
    public const string DEFAULT = '1';

    public function __construct(
        string $redisDsn,
        ContextInterface $context,
    ) {
        $redisDsn = str_replace('/0', '/1', $redisDsn);
        parent::__construct($redisDsn, $context);
    }

    public function deleteKey(string $key): void
    {
        $this->removeItem($key);
    }

    public function executeIfNotExists(string $key, callable $callback, int $lifetime = 300): void
    {
        if ($this->hasKey($key)) {
            return;
        }

        $this->saveKey($key, $lifetime);
        $callback();
    }

    public function hasKey(string $key): bool
    {
        $value = $this->getItem($key);

        return $value !== null;
    }

    public function readInt(string $key, int $default = 0): int
    {
        return (int) ($this->getItem($key) ?? $default);
    }

    public function readString(string $key, string $default = ''): string
    {
        return (string) ($this->getItem($key) ?? $default);
    }

    public function refreshKey(string $key, int $lifetime = 300): bool
    {
        $exists = !$this->hasKey($key);
        $value = $this->getItem($key) ?? self::DEFAULT;
        $this->setItem($key, $value, $lifetime);

        return $exists;
    }

    public function saveInt(string $key, int $value, int $ttl = 300): void
    {
        $this->setItem($key, $value, $ttl);
    }

    public function saveKey(string $key, int $lifetime = 300): void
    {
        $this->setItem($key, self::DEFAULT, $lifetime);
    }

    public function saveString(string $key, string $value, int $ttl = 300): void
    {
        $this->setItem($key, $value, $ttl);
    }
}
