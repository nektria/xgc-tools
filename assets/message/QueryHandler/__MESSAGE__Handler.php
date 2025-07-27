<?php

declare(strict_types=1);

namespace App\MessageHandler\__ENTITY__;

use App\Document\__ENTITY__;
use App\Infrastructure\ReadModel\__ENTITY__ReadModel;
use App\Message\__ENTITY__\__MESSAGE__;
use Xgc\Exception\BaseException;
use Xgc\Message\MessageHandler;

readonly class __MESSAGE__Handler extends MessageHandler
{
    public function __construct(
        private __ENTITY__ReadModel $__ENTITY_CC__IdReadModel,
    )
    {
    }

    public function __invoke(__MESSAGE__ $message): __ENTITY__
    {
        throw new BaseException('Not implemented');
    }
}
