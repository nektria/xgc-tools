<?php

declare(strict_types=1);

namespace Xgc\Exception;

class MissingArgumentException extends BaseException
{
    public function __construct(string $field)
    {
        parent::__construct(
            "Field '{$field}' is missing.",
            status: 400,
            extras: [
                'field' => $field,
                'type' => 'MISSING_ARGUMENT'
            ]
        );
    }
}
