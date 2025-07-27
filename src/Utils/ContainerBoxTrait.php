<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;

use function define;

define('CONTAINER_BOX', ContainerBox::instance());
trait ContainerBoxTrait
{
    private const ContainerBox CONTAINER = CONTAINER_BOX;

    public function setContainer(?ContainerInterface $container): void
    {
        self::CONTAINER->setContainer($container);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function get(string $class): object
    {
        return self::CONTAINER->get($class);
    }

    private function getParameter(string $name): ?string
    {
        return self::CONTAINER->getParameter($name);
    }
}
