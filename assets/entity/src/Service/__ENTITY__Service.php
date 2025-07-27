<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\__ENTITY__;
use App\Infrastructure\WriteModel\__ENTITY__WriteModel;
use Nektria\Exception\ResourceNotFoundException;
use Nektria\Service\AbstractService;

readonly class __ENTITY__Service extends AbstractService
{
    public function __construct(
        private __ENTITY__WriteModel $__ENTITY_CC__WriteModel,
    ) {
        parent::__construct();
    }

    public function delete(__ENTITY__ $__ENTITY_CC__): void
    {
        $this->__ENTITY_CC__WriteModel->delete($__ENTITY_CC__);
    }

    public function get(string $__ENTITY_CC__Id): ?__ENTITY__
    {
        return $this->__ENTITY_CC__WriteModel->find($__ENTITY_CC__Id);
    }

    public function retrieve(string $__ENTITY_CC__Id): __ENTITY__
    {
        $__ENTITY_CC__ = $this->get($__ENTITY_CC__Id);

        if ($__ENTITY_CC__ === null) {
            throw new ResourceNotFoundException('__ENTITY__', $__ENTITY_CC__Id);
        }

        return $__ENTITY_CC__;
    }

    public function save(__ENTITY__ $__ENTITY_CC__): void
    {
        $this->__ENTITY_CC__WriteModel->save($__ENTITY_CC__);
    }
}
