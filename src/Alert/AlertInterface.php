<?php

declare(strict_types=1);

namespace Xgc\Alert;

use Throwable;
use Xgc\Dto\DocumentInterface;

interface AlertInterface
{
    public function publishDebugMessage(
        string $channelId,
        string $message,
    ): void;

    public function publishMessage(
        string $channelId,
        string $message,
    ): void;

    public function publishThrowable(Throwable $throwable, ?DocumentInterface $input = null): void;
}
