<?php

declare(strict_types=1);

namespace Xgc\Symfony\Kernel;

use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Xgc\Utils\ContainerBoxTrait;

use function in_array;

class ContainerBoxPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $class = $definition->getClass();

            if ($class === null || !class_exists($class, false)) {
                continue;
            }

            $traits = new ReflectionClass($class)->getTraitNames();

            if (in_array(ContainerBoxTrait::class, $traits, true)) {
                $definition->addMethodCall('setContainer', [new Reference('service_container')]);
            }
        }
    }
}
