<?php

declare(strict_types=1);

namespace Xgc\Symfony\Listener;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Xgc\Alert\AlertInterface;
use Xgc\Dto\ArrayDocument;
use Xgc\Log\Logger;

readonly abstract class ConsoleListener implements EventSubscriberInterface
{
    public function __construct(
        private AlertInterface $alertService,
        private Logger $logger,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::ERROR => 'onConsoleError',
        ];
    }

    public function onConsoleError(ConsoleErrorEvent $event): void
    {
        $command = $event->getCommand();

        if ($command === null) {
            return;
        }

        $fixedClassName = str_replace('\\', '/', $command::class);
        $path = "/Console/{$fixedClassName}";
        $args = $event->getInput()->getArguments();
        $options = $event->getInput()->getOptions();

        $info = new ArrayDocument([
            'path' => $path,
            'command' => $args['command'],
            'arguments' => $args['receivers'] ?? [],
            'options' => $options,
        ]);

        $this->logger->exception($event->getError(), $info);
        $this->alertService->publishThrowable($event->getError(), $info);
    }
}
