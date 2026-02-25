<?php

declare(strict_types=1);

namespace Xgc\Exception;

use function gettype;

class InvalidArgumentException extends BaseException
{
    public function __construct(string $field, mixed $value, ?string $condition = null)
    {
        parent::__construct(
            $condition === null
                ? "Invalid field '{$field}' with value '{$this->getShortValue($value)}'."
                : "Invalid field '{$field} ({$condition})' with value '{$this->getShortValue($value)}'.",
            status: 400,
            extras: [
                'field' => $field,
                'value' => $value,
                'type' => 'INVALID_FIELD'
            ]
        );
    }

    private function getShortValue(mixed $value): string
    {
        $type = gettype($value);

        return match ($type) {
            'array' => 'array',
            'object' => 'object',
            'resource' => 'resource',
            'resource (closed)' => 'resource (closed)',
            'NULL' => 'null',
            default => $type,
        };
    }
}
