<?php

declare(strict_types=1);

namespace Xgc\Log;

use Xgc\Cache\SharedRedisCache;
use Xgc\Dto\ContextInterface;

use function array_slice;

/**
 * @phpstan-type CachedLog array{
 *     payload: mixed[],
 *     message: string,
 *     project: string,
 *     labels: string[] | null,
 * }
 *
 * @extends SharedRedisCache<CachedLog[]>
 */
class SharedLogCache extends SharedRedisCache
{
    public function __construct(
        private readonly ContextInterface $context,
        string $redisDsn,
    ) {
        parent::__construct($redisDsn, $this->context);
    }

    /**
     * @param CachedLog $log
     */
    public function addLog(array $log): void
    {
        $logs = $this->getItem($this->context->traceId()) ?? [];
        $logs[] = $log;
        $logs = array_slice($logs, -20);

        $this->setItem($this->context->traceId(), $logs, 300);
    }

    /**
     * @return CachedLog[]
     */
    public function getLogs(): array
    {
        $logs = $this->getItem($this->context->traceId()) ?? [];
        $this->removeItem($this->context->traceId());

        return $logs;
    }
}
