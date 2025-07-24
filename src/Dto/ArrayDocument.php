<?php

declare(strict_types=1);

namespace Xgc\Dto;

readonly class ArrayDocument extends Document
{
    /**
     * @param mixed[] $data
     */
    public function __construct(public array $data = [])
    {
    }

    /**
     * @return mixed[]
     */
    public function data(): array
    {
        return $this->data;
    }

    public function toArray(ContextInterface $context): array
    {
        return $this->data;
    }
}
