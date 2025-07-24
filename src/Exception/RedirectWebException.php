<?php

namespace Xgc\Exception;

use Throwable;

class RedirectWebException extends BaseException
{
    public function __construct(
        public readonly string $path, int $status = 302)
    {
        parent::__construct($path, $status);
    }
}
