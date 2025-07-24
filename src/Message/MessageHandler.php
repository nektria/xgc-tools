<?php

declare(strict_types=1);

namespace Xgc\Message;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Xgc\Utils\ContainerBox;

use function define;

define('MESSAGE_HANDLER_CONTAINER_BOX', new ContainerBox());

readonly abstract class MessageHandler
{
    public const ContainerBox CONTAINER_BOX = MESSAGE_HANDLER_CONTAINER_BOX;

    public function setContainer(ContainerInterface $container): void
    {
        self::CONTAINER_BOX->setContainer($container);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    protected function get(string $class): object
    {
        return self::CONTAINER_BOX->get($class);
    }
}
