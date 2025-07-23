<?php

declare(strict_types=1);

namespace Xgc\DB\Type;

use Xgc\Dto\Metadata;
use Xgc\Utils\JsonUtil;

/**
 * @extends BaseJsonType<Metadata>
 */
class MetadataType extends BaseJsonType
{
    /**
     * @param Metadata $phpValue
     */
    protected function convertToDatabase($phpValue): string
    {
        return JsonUtil::encode($phpValue->data());
    }

    protected function convertToPhp(string $databaseValue): Metadata
    {
        return new Metadata(JsonUtil::decode($databaseValue));
    }

    protected function getTypeName(): string
    {
        return 'metadata';
    }
}
