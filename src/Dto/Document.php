<?php

declare(strict_types=1);

namespace Xgc\Dto;

readonly abstract class Document implements DocumentInterface
{
    public function value(string $name): mixed
    {
        if (property_exists($this, $name)) {
            /* @phpstan-ignore-next-line */
            return $this->$name;
        }

        return null;
    }
}
