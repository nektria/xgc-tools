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

    public function inject(ContainerInterface $container): void
    {
        self::CONTAINER_BOX->set($container);
    }

    protected function containerBox(): ContainerBox
    {
        return self::CONTAINER_BOX;
    }
}
