<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Xgc\Exception\BaseException;

class ContainerBox
{
    use ContainerBoxTrait;

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function get(string $class): object
    {
        if ($this->container === null) {
            throw new BaseException('Container not set.');
        }

        /** @var T $service */
        $service = $this->container->get($class);

        return $service;
    }
}
