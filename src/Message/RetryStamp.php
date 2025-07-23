<?php

declare(strict_types=1);

namespace Xgc\Message;

use Symfony\Component\Messenger\Stamp\StampInterface;

readonly class RetryStamp implements StampInterface
{
    public function __construct(
        public int $currentTry,
        public int $maxRetries,
        public int $intervalMs
    ) {
    }
}
