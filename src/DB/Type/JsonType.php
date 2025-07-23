<?php

declare(strict_types=1);

namespace Xgc\DB\Type;

use Xgc\Utils\JsonUtil;

/**
 * @extends BaseJsonType<array<mixed>>
 */
class JsonType extends BaseJsonType
{
    /**
     * @param array<mixed> $phpValue
     */
    protected function convertToDatabase($phpValue): string
    {
        return JsonUtil::encode($phpValue);
    }

    /**
     * @return array<mixed>
     */
    protected function convertToPhp(string $databaseValue): array
    {
        return JsonUtil::decode($databaseValue);
    }

    protected function getTypeName(): string
    {
        return 'better_json';
    }
}
