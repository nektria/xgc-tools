<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;
use UnitEnum;
use Xgc\Exception\BaseException;

use function is_array;

class ContainerBox
{
    private static ?self $instance = null;

    private ?ContainerInterface $container = null;

    final public static function instance(): self
    {
        self::$instance ??= new self();

        return self::$instance;
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

    public function getParameter(string $name): ?string
    {
        if ($this->container === null) {
            throw new BaseException('Container not set.');
        }

        $value = $this->container->getParameter($name);

        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return JsonUtil::encode($value);
        }

        if ($value instanceof UnitEnum) {
            return null;
        }

        return (string) $value;
    }

    public function setContainer(?ContainerInterface $container): void
    {
        $this->container = $container;
    }
}
