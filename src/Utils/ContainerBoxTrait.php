<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use UnitEnum;

use function define;

define('CONTAINER_BOX', ContainerBox::instance());
trait ContainerBoxTrait
{
    private const ContainerBox CONTAINER = CONTAINER_BOX;

    public function setContainer(?ContainerInterface $container): void
    {
        /** @var ContainerBox $ctr */
        $ctr = self::CONTAINER;
        $ctr->setContainer($container);
    }

    /**
     * @return array<mixed>|bool|float|int|string|UnitEnum|null
     */
    private function parameter(string $name): array | bool | float | int | string | UnitEnum | null
    {
        /** @var ContainerBox $ctr */
        $ctr = self::CONTAINER;

        return $ctr->getParameter($name);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function service(string $class): object
    {
        /** @var ContainerBox $ctr */
        $ctr = self::CONTAINER;

        return $ctr->get($class);
    }
}
