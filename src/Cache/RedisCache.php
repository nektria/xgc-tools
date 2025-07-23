<?php

declare(strict_types=1);

namespace Xgc\Cache;

use Redis;
use Throwable;
use Xgc\Dto\ContextInterface;
use Xgc\Exception\BaseException;

use function count;

abstract class RedisCache
{
    private static ?Redis $connection = null;

    public readonly string $fqn;

    private readonly string $redisDsn;

    public function __construct(
        string $redisDsn,
        ContextInterface $context,
        string $redisPrefix,
    ) {
        $parts = explode('\\', static::class);
        $name = substr(end($parts), 0, -5);
        $this->fqn = "{$redisPrefix}_{$name}_{$context->env()}";
        $this->redisDsn = $redisDsn;
    }

    public function decr(string $key): void
    {
        try {
            $this->init()->decr($key);
        } catch (Throwable $e) {
            throw BaseException::extends($e);
        }
    }

    public function empty(): void
    {
        // empty
    }

    public function incr(string $key): void
    {
        try {
            $this->init()->incr($key);
        } catch (Throwable $e) {
            throw BaseException::extends($e);
        }
    }

    /**
     * @return int[]
     */
    public function size(): array
    {
        try {
            $it = null;
            $count = 0;
            $size = 0;

            do {
                $keys = $this->init()->scan($it, "{$this->fqn}:*", 1000);

                if ($keys !== false) {
                    $count += count($keys);
                    if ($size === 0 && count($keys) > 0) {
                        try {
                            $memoryUsage = $this->init()->rawcommand('MEMORY', 'USAGE', $keys[0]);
                            $size += $memoryUsage;
                        } catch (Throwable) {
                        }
                    }
                }
            } while ((int) $it > 0);

            return [$count, $size * $count];
        } catch (Throwable $e) {
            throw BaseException::extends($e);
        }
    }

    protected function init(): Redis
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        try {
            $parts = parse_url($this->redisDsn);
            $redis = new Redis();

            $redis->pconnect(
                $parts['host'] ?? 'localhost',
                $parts['port'] ?? 6379,
                0,
                (string) getenv('HOSTNAME'),
            );

            if (isset($parts['pass'])) {
                $redis->auth($parts['pass']);
            }

            $redis->select(0);
        } catch (Throwable $e) {
            throw BaseException::extends($e);
        }

        self::$connection = $redis;

        return self::$connection;
    }
}
