<?php

declare(strict_types=1);

namespace App\Infrastructure\WriteModel;

use App\Entity\__ENTITY__;
use Nektria\Infrastructure\WriteModel;

/**
 * @extends WriteModel<__ENTITY__>
 */
class __ENTITY__WriteModel extends WriteModel
{
    public function delete(__ENTITY__ $__ENTITY_CC__): void
    {
        $this->deleteEntity($__ENTITY_CC__);
    }

    public function find(string $__ENTITY_CC__Id): ?__ENTITY__
    {
        return $this->findEntity($__ENTITY_CC__Id);
    }

    public function save(__ENTITY__ $__ENTITY_CC__): void
    {
        $this->saveEntity($__ENTITY_CC__);
    }

    protected function getClassName(): string
    {
        return __ENTITY__::class;
    }
}
