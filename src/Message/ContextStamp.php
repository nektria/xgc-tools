<?php

declare(strict_types=1);

namespace Xgc\Message;

use Symfony\Component\Messenger\Stamp\StampInterface;

readonly class ContextStamp implements StampInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public string $traceId,
        public string $context,
        public array $data,
    ) {
    }
}
