<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Xgc\Exception\BaseException;

trait ContainerBoxTrait
{
    private ?ContainerInterface $container = null;

    public function setContainer(?ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function get(string $class): object
    {
        if ($this->container === null) {
            throw new BaseException('Container not set.');
        }

        /** @var T $service */
        $service = $this->container->get($class);

        return $service;
    }
}
