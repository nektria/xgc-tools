<?php

declare(strict_types=1);

namespace Xgc\Dto;

readonly class ArrayDocument extends Document
{
    public function __construct(public mixed $data)
    {
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function toArray(ContextInterface $context): array
    {
        return $this->data;
    }
}
