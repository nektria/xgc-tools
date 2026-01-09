<?php

declare(strict_types=1);

namespace Xgc\Symfony\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Xgc\Exception\BaseException;

class ServiceLoader
{
    private static ?ContainerInterface $container = null;

    public function __construct(ContainerInterface $container)
    {
        self::$container = $container;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public static function service(string $class): object
    {
        if (self::$container === null) {
            throw new BaseException('Container not set.');
        }

        /** @var T $service */
        $service = self::$container->get($class);

        return $service;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function get(string $class): object
    {
        if (self::$container === null) {
            throw new BaseException('Container not set.');
        }

        /** @var T $service */
        $service = self::$container->get($class);

        return $service;
    }
}
