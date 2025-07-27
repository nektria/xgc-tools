<?php

declare(strict_types=1);

namespace App\Infrastructure\ReadModel;

use App\Document\__ENTITY__;
use Xgc\DB\ReadModel;
use Xgc\Dto\Clock;
use Xgc\Dto\Document;
use Xgc\Exception\ResourceNotFoundException;

/**
 * @extends ReadModel<__ENTITY__>
 */
class __ENTITY__ReadModel extends ReadModel
{
    public function opt(string $__ENTITY_CC__Id): ?__ENTITY__
    {
        return $this->getResult(
            'WHERE id=:__ENTITY_CC__Id',
            [
                '__ENTITY_CC__Id' => $__ENTITY_CC__Id
            ],
        );
    }

    public function read(string $__ENTITY_CC__Id): __ENTITY__
    {
        $data = $this->opt($__ENTITY_CC__Id);

        if ($data === null) {
            throw new ResourceNotFoundException('__ENTITY__', $__ENTITY_CC__Id);
        }

        return $data;
    }

    protected function buildDocument(array $params): Document
    {
        return new __ENTITY__(
            id: $params['id'],
            createdAt: Clock::fromString($params['created_at']),
            updatedAt: Clock::fromString($params['updated_at']),
        );
    }

    protected function source(): string
    {
        return '
            SELECT *
            FROM __ENTITY_SC__
        ';
    }
}
