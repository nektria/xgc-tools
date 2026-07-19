<?php

declare(strict_types=1);

namespace Xgc\Dto;

readonly abstract class Document implements DocumentInterface
{
    public function value(string $name): ?string
    {
        if (property_exists($this, $name)) {
            /* @phpstan-ignore-next-line */
            return (string) $this->$name;
        }

        return null;
    }
}
