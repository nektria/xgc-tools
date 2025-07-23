<?php

declare(strict_types=1);

namespace Xgc\Message;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Xgc\Utils\ContainerBox;
use Xgc\Utils\ContainerBoxTrait;

use function define;

define('MESSAGE_HANDLER_CONTAINER_BOX', new ContainerBox());

readonly abstract class MessageHandler
{
    use ContainerBoxTrait;

    public const ContainerBox CONTAINER_BOX = MESSAGE_HANDLER_CONTAINER_BOX;

    public function set(ContainerInterface $container): void
    {
        self::CONTAINER_BOX->set($container);
    }

    protected function containerBox(): ContainerBox
    {
        return self::CONTAINER_BOX;
    }
}
