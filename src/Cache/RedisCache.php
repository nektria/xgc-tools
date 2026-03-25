<?php

declare(strict_types=1);

namespace Xgc\Cache;

use Redis;
use Throwable;
use Xgc\Dto\ContextInterface;
use Xgc\Exception\BaseException;

use function count;
use function is_int;

abstract class RedisCache
{
    public readonly string $fqn;

    private static ?Redis $connection = null;

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

    public function empty(): void
    {
        // empty
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
                            if (is_int($memoryUsage)) {
                                $size += $memoryUsage;
                            }
                        } catch (Throwable) {
                        }
                    }
                }

                if (!is_int($it)) {
                    break;
                }
            } while ($it > 0);

            return [$count, $size * $count];
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    protected function decr(string $key): void
    {
        try {
            $this->init()->decr($key);
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    protected function incr(string $key): void
    {
        try {
            $this->init()->incr($key);
        } catch (Throwable $e) {
            throw BaseException::extend($e);
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
            throw BaseException::extend($e);
        }

        self::$connection = $redis;

        return self::$connection;
    }
}
