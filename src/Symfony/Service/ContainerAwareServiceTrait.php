<?php

declare(strict_types=1);

namespace Xgc\Symfony\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use UnitEnum;
use Xgc\Exception\BaseException;

trait ContainerAwareServiceTrait
{
    /**
     * @var (ContainerInterface|null)[]
     */
    private array $containerBag;

    public function setContainer(ContainerInterface $containerBag): void
    {
        $this->containerBag[0] = $containerBag;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function get(string $class): object
    {
        if ($this->containerBag[0] === null) {
            throw new BaseException('Container not set');
        }

        /** @var T $clss */
        $clss = $this->containerBag[0]->get($class);

        return $clss;
    }

    /**
     * @return array<mixed>|bool|float|int|string|UnitEnum|null
     */
    private function getParameter(string $name): array | bool | float | int | string | UnitEnum | null
    {
        if ($this->containerBag[0] === null) {
            throw new BaseException('Container not set');
        }

        return $this->containerBag[0]->getParameter($name);
    }

    private function initContainerAwareServiceTrait(): void
    {
        $this->containerBag = [null];
    }
}
