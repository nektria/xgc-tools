<?php

declare(strict_types=1);

namespace Xgc\Exception;

class ResourceNotFoundException extends BaseException
{
    public function __construct(string $resourceType, ?string $ref)
    {
        parent::__construct(
            message: $ref === null
                ? "{$resourceType} not found."
                : "{$resourceType} '{$ref}' not found.",
            status: 404,
            extras: [
                'resource' => $resourceType,
                'ref' => $ref,
                'type' => 'RESOURCE_NOT_FOUND'
            ]
        );
    }
}
