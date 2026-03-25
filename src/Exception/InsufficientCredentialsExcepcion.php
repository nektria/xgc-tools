<?php

declare(strict_types=1);

namespace Xgc\Exception;

class InsufficientCredentialsExcepcion extends BaseException
{
    public function __construct()
    {
        parent::__construct('Access denied.', 403);
    }
}
