<?php

declare(strict_types=1);

namespace App\Message\__ENTITY__;

use Xgc\Message\Event;

readonly class __MESSAGE__ implements Event
{
    public function __construct(
        public string $__ENTITY_CC__Id,
    ) {
    }

    public function ref(): string
    {
        return $this->__ENTITY_CC__Id;
    }
}
