<?php

declare(strict_types=1);

namespace Xgc\Dto;

interface DocumentInterface
{
    /**
     * @return mixed[]
     */
    public function toArray(?ContextInterface $context): array;
}
