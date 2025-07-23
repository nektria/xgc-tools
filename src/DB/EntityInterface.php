<?php

declare(strict_types=1);

namespace Xgc\DB;

interface EntityInterface
{
    public function id(): string;

    public function refresh(): void;
}
