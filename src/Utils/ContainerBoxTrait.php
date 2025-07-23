<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Xgc\Exception\BaseException;

use function define;

define('MESSAGE_HANDLER_CONTAINER_BOX', new ContainerBox());

trait ContainerBoxTrait
{
    /** @var (ContainerInterface|null)[] */
    private const array CONTAINERS = [null];

    public function setContainer(?ContainerInterface $container): void
    {
        self::CONTAINERS[0] = $container;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function get(string $class): object
    {
        /** @var ContainerInterface|null $container */
        $container = self::CONTAINERS[0];

        if ($container === null) {
            throw new BaseException('Container not set.');
        }

        /** @var T $service */
        $service = $container->get($class);

        return $service;
    }
}
