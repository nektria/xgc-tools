<?php

declare(strict_types=1);

namespace Xgc\Exception;

class AccessDeniedExcepcion extends BaseException
{
    public function __construct()
    {
        parent::__construct('Access denied.', 401);
    }
}
