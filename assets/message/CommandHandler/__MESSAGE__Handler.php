<?php

declare(strict_types=1);

namespace App\MessageHandler\__ENTITY__;

use App\Message\__ENTITY__\__MESSAGE__;
use Xgc\Exception\BaseException;
use Xgc\Message\MessageHandler;

readonly class __MESSAGE__Handler extends MessageHandler
{
    public function __invoke(__MESSAGE__ $message): void
    {
        throw new BaseException('Not implemented');
    }
}
