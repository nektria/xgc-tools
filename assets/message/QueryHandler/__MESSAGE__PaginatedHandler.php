<?php

declare(strict_types=1);

namespace App\MessageHandler\__ENTITY__;

use App\Document\__ENTITY__;
use App\Infrastructure\ReadModel\__ENTITY__ReadModel;
use App\Message\__ENTITY__\__MESSAGE__;
use Xgc\Dto\PaginatedDocumentCollection;
use Xgc\Exception\BaseException;
use Xgc\Message\MessageHandler;

readonly class __MESSAGE__Handler extends MessageHandler
{
    public function __construct(
        private __ENTITY__ReadModel $__ENTITY_CC__ReadModel,
    )
    {
    }

    /**
     * @param __MESSAGE__ $message
     * @return PaginatedDocumentCollection<__ENTITY__>
     */
    public function __invoke(__MESSAGE__ $message): PaginatedDocumentCollection
    {
        throw new BaseException('Not implemented');
    }
}
