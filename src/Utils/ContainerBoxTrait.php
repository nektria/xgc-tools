<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use UnitEnum;

trait ContainerBoxTrait
{
    private ?ContainerBox $container;

    public function setContainer(?ContainerInterface $container): void
    {
        $this->container ??= ContainerBox::instance();
        $this->container->setContainer($container);
    }

    /**
     * @return array<mixed>|bool|float|int|string|UnitEnum|null
     */
    private function parameter(string $name): array | bool | float | int | string | UnitEnum | null
    {
        $this->container ??= ContainerBox::instance();

        return $this->container->getParameter($name);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function service(string $class): object
    {
        $this->container ??= ContainerBox::instance();

        return $this->container->get($class);
    }
}
