<?php

declare(strict_types=1);

namespace Xgc\Exception;

use Throwable;

class DomainException extends BaseException
{
    public function __construct(
        string $message,
        ?array $extras = null,
        array $options = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 409, $extras, $options, $previous);
    }
}
