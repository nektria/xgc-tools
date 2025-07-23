<?php

declare(strict_types=1);

namespace Xgc\Message;

use Symfony\Component\Messenger\Stamp\DelayStamp;
use Xgc\Dto\Document;
use Xgc\Dto\DocumentInterface;

interface BusInterface
{
    public function dispatchCommand(
        Command $command,
        ?string $transport = null,
        ?DelayStamp $delayMs = null,
        ?RetryStamp $retryOptions = null
    ): void;

    public function dispatchEvent(
        Event $command,
        ?string $transport = null,
        ?DelayStamp $delayMs = null,
        ?RetryStamp $retryOptions = null
    ): void;

    /**
     * @template T of Document
     * @param Query<T> $query
     */
    public function dispatchQuery(Query $query): DocumentInterface;
}
