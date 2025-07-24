<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Xgc\Exception\BaseException;

class ContainerBox
{
    private ?ContainerInterface $container = null;

    final public static function instance(): self
    {
        return new self();
    }

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

    public function setContainer(?ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
