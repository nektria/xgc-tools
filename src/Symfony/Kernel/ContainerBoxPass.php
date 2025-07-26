<?php

declare(strict_types=1);

namespace Xgc\Symfony\Kernel;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Xgc\Utils\ContainerBoxTrait;

use function in_array;

class ContainerBoxPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $class = $definition->getClass();
            if ($class === null || !class_exists($class)) {
                continue;
            }

            if (in_array(ContainerBoxTrait::class, class_uses($class), true)) {
                $definition->addMethodCall('setContainer', [$container]);
            }
        }
    }
}
