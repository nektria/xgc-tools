<?php

declare(strict_types=1);

namespace Xgc\Exception;

class RedirectWebException extends BaseException
{
    public function __construct(
        public readonly string $path,
        int $status = 302
    ) {
        parent::__construct($path, status: $status);
    }
}
