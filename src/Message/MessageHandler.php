<?php

declare(strict_types=1);

namespace Xgc\Message;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Xgc\Utils\ContainerBox;
use Xgc\Utils\ContainerBoxTrait;

use function define;

define('MESSAGE_HANDLER_CONTAINER', new ContainerBox());

readonly abstract class MessageHandler
{
    use ContainerBoxTrait;

    public function setContainer(ContainerInterface $container): void
    {
        self::CONTAINER->setContainer($container);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    protected function get(string $class): object
    {
        return self::CONTAINER->get($class);
    }
}
