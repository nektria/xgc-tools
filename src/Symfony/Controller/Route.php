<?php

declare(strict_types=1);

namespace Xgc\Symfony\Controller;

use Attribute;

#[Attribute]
class Route extends \Symfony\Component\Routing\Attribute\Route
{
    public function __construct(string $path = '', ?string $method = null)
    {
        $parts = explode('/', $path);
        $requirements = [];

        foreach ($parts as $part) {
            if (!str_ends_with($part, 'Id}')) {
                continue;
            }

            $name = str_replace(['{', '}'], '', $part);

            $requirements[$name] = '^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$';
        }

        parent::__construct(
            $path,
            requirements: $requirements,
            methods: $method === null ? [] : [$method],
        );
    }
}
