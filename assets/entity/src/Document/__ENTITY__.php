<?php

declare(strict_types=1);

namespace App\Document;

use Nektria\Document\Document;
use Nektria\Dto\Clock;
use Nektria\Service\ContextService;

readonly class __ENTITY__ extends Document
{
    public function __construct(
        public string $id,
        public Clock $createdAt,
        public Clock $updatedAt,
    ) {
    }

    public function toArray(ContextService $context): array
    {
        return [
            'id' => $this->id,
            'createdAt' => $this->createdAt->iso8601String(),
            'updatedAt' => $this->updatedAt->iso8601String(),
        ];
    }
}
