<?php

declare(strict_types=1);

namespace Xgc\Symfony\Listener;

use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpReceivedStamp;
use Symfony\Component\Messenger\Event\SendMessageToTransportsEvent;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Throwable;
use Xgc\Alert\AlertInterface;
use Xgc\Cache\SharedVariableCache;
use Xgc\Dto\ArrayDocument;
use Xgc\Dto\Clock;
use Xgc\Dto\ContextInterface;
use Xgc\Enums\LogLevel;
use Xgc\Exception\BaseException;
use Xgc\Log\LoggerInterface;
use Xgc\Log\ProcessRegistry;
use Xgc\Message\BusInterface;
use Xgc\Message\Command;
use Xgc\Message\ContextStamp;
use Xgc\Message\Event;
use Xgc\Message\MessageInterface;
use Xgc\Message\RetryStamp;
use Xgc\Symfony\Service\ContainerAwareServiceTrait;
use Xgc\Utils\JsonUtil;
use Xgc\Utils\StringUtil;

use function in_array;

abstract class MessageListener implements EventSubscriberInterface
{
    use ContainerAwareServiceTrait;

    private float $executionStartedAt;

    private string $messageCompletedAt;

    private string $messageStartedAt;

    public function __construct()
    {
        $this->executionStartedAt = microtime(true);
        $this->messageCompletedAt = Clock::now()->iso8601String();
        $this->messageStartedAt = $this->messageCompletedAt;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SendMessageToTransportsEvent::class => 'onSendMessageToTransports',
            WorkerMessageReceivedEvent::class => 'onWorkerMessageReceived',
            WorkerMessageHandledEvent::class => 'onWorkerMessageHandled',
            WorkerMessageFailedEvent::class => 'onMessengerException',
        ];
    }

    public function onMessengerException(WorkerMessageFailedEvent $event): void
    {
        $bus = $this->service(BusInterface::class);
        $logger = $this->service(LoggerInterface::class);
        $alertService = $this->service(AlertInterface::class);
        $processRegistry = $this->service(ProcessRegistry::class);

        try {
            $retryStamp = $event->getEnvelope()->last(RetryStamp::class);
            $transportStamp = $event->getEnvelope()->last(TransportNamesStamp::class);
            $this->messageCompletedAt = Clock::now()->iso8601String();
            $message = $event->getEnvelope()->getMessage();

            if (!($message instanceof Command || $message instanceof Event)) {
                return;
            }

            $maxRetries = 1;
            $error = BaseException::extendAndThrow($event->getThrowable());
            $this->decreaseCounter($message);

            $transport = null;
            if ($transportStamp !== null) {
                $transport = $transportStamp->getTransportNames()[0];
            }

            if ($error->retryWhenAsync) {
                $maxRetries = 10;
                $nextTry = 1;
                $intervalMs = 60000;

                if ($retryStamp !== null) {
                    $maxRetries = $retryStamp->maxRetries;
                    $nextTry = $retryStamp->currentTry + 1;
                    $intervalMs = $retryStamp->intervalMs;
                }

                if ($message instanceof Command) {
                    $bus->dispatchCommand(
                        $message,
                        transport: $transport,
                        retryOptions: new RetryStamp($nextTry, $maxRetries, $intervalMs),
                    );
                } else {
                    $bus->dispatchEvent(
                        $message,
                        transport: $transport,
                        retryOptions: new RetryStamp($nextTry, $maxRetries, $intervalMs),
                    );
                }
            }

            $data = JsonUtil::deserializeMessage($message);
            $exception = $event->getThrowable();
            $class = $message::class;
            $messageClass = StringUtil::className($message);

            while ($exception instanceof HandlerFailedException) {
                $exception = $exception->getPrevious();
            }

            if ($exception instanceof BaseException && $exception->getPrevious() !== null) {
                $exception = $exception->getPrevious();
            }

            if ($exception === null) {
                return;
            }

            if (
                $exception instanceof DriverException
                || $exception instanceof ConnectionException
            ) {
                exit(1);
            }

            $exchangeName = 'unknown';
            $exchangeStamp = $event->getEnvelope()->last(AmqpReceivedStamp::class);
            if ($exchangeStamp !== null) {
                $exchangeName = $exchangeStamp->getAmqpEnvelope()->getExchangeName();
            }

            $logger->temporalLogs();
            $logger->exception(
                throwable: $exception,
                input: new ArrayDocument([
                    'context' => 'messenger',
                    'code' => $this->normalizeClass($class),
                    'body' => $data,
                    'messageReceivedAt' => $this->messageStartedAt,
                    'messageCompletedAt' => $this->messageCompletedAt,
                    'queue' => $exchangeName,
                    'maxRetries' => $maxRetries,
                    'httpRequest' => [
                        'requestUrl' => "/{$messageClass}/{$message->ref()}",
                        'requestMethod' => 'QUEUE',
                        'status' => 500,
                        'latency' => max(0.001, round(microtime(true) - $this->executionStartedAt, 3)) . 's',
                    ],
                ]),
            );

            $ignoreMessages = [
                'Redelivered message from AMQP detected that will be rejected and trigger the retry logic.',
            ];

            if (!in_array($exception->getMessage(), $ignoreMessages, true)) {
                $alertService->publishThrowable(
                    $event->getThrowable(),
                    input: new ArrayDocument([
                        'message' => $this->normalizeClass($class),
                        'body' => $data,
                    ])
                );
            }

            $processRegistry->clear();
            $this->cleanMemory();
            gc_collect_cycles();
        } catch (Throwable) {
        }
    }

    public function onSendMessageToTransports(SendMessageToTransportsEvent $event): void
    {
        $message = $event->getEnvelope()->getMessage();

        if ($message instanceof MessageInterface) {
            $this->increasePendingCounter($message);
        }
    }

    public function onWorkerMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $logger = $this->service(LoggerInterface::class);
        $processRegistry = $this->service(ProcessRegistry::class);

        $this->messageCompletedAt = Clock::now()->iso8601String();
        $message = $event->getEnvelope()->getMessage();

        if ($message instanceof MessageInterface) {
            $this->decreaseCounter($message);
        }

        $exchangeName = 'unknown';
        $exchangeStamp = $event->getEnvelope()->last(AmqpReceivedStamp::class);
        if ($exchangeStamp !== null) {
            $exchangeName = $exchangeStamp->getAmqpEnvelope()->getExchangeName() ?? 'unknown';
        }

        $logLevel = $this->assignLogLevel($this->normalizeClass($message::class));

        if ($logLevel !== LogLevel::NONE && ($message instanceof MessageInterface)) {
            $data = JsonUtil::deserializeMessage($message);
            $messageClass = StringUtil::className($message);
            $resume = "/{$messageClass}";
            if ($message instanceof Command || $message instanceof Event) {
                $resume = "/{$messageClass}/{$message->ref()}";
            }

            $time = max(0.001, round(microtime(true) - $this->executionStartedAt, 3)) . 's';

            $processRegistry->addValue('context', 'messenger');
            $processRegistry->addValue('path', $this->normalizeClass($message::class));
            $processRegistry->addValue('queue', $exchangeName);

            $logger->log(
                $logLevel,
                [
                    'body' => $data,
                    'executionTime' => $time,
                    'httpRequest' => [
                        'requestUrl' => $resume,
                        'requestMethod' => 'QUEUE',
                        'status' => 200,
                        'latency' => $time,
                    ],
                    'messageReceivedAt' => $this->messageStartedAt,
                    'messageCompletedAt' => $this->messageCompletedAt,
                ],
                [],
                $resume,
            );
        }

        $processRegistry->clear();
        $this->cleanMemory();
        gc_collect_cycles();
    }

    public function onWorkerMessageReceived(WorkerMessageReceivedEvent $event): void
    {
        $processRegistry = $this->service(ProcessRegistry::class);
        $context = $this->service(ContextInterface::class);

        $message = $event->getEnvelope()->getMessage();
        $exchangeName = '?';
        $exchangeStamp = $event->getEnvelope()->last(AmqpReceivedStamp::class);
        if ($exchangeStamp !== null) {
            $exchangeName = $exchangeStamp->getAmqpEnvelope()->getExchangeName() ?? '?';
        }

        $processRegistry->clear();
        $processRegistry->getMetadata()->updateField('context', 'messenger');
        $processRegistry->getMetadata()->updateField('path', $this->normalizeClass($message::class));
        $processRegistry->getMetadata()->updateField('queue', $exchangeName);

        $message = $event->getEnvelope()->getMessage();
        if ($message instanceof MessageInterface) {
            $this->increaseCounter($message);
            $this->decreasePendingCounter($message);
        }

        /** @var ContextStamp|null $contextStamp */
        $contextStamp = $event->getEnvelope()->last(ContextStamp::class);
        if ($contextStamp !== null) {
            $context->setTraceId($contextStamp->traceId);
            $context->setExtras($contextStamp->data);
        }

        $this->messageStartedAt = Clock::now()->iso8601String();
        $this->executionStartedAt = microtime(true);
    }

    abstract protected function assignLogLevel(string $code): LogLevel;

    protected function cleanMemory(): void
    {
    }

    private function decreaseCounter(MessageInterface $message): void
    {
        $context = $this->service(ContextInterface::class);
        $sharedVariableCache = $this->service(SharedVariableCache::class);

        $project = $context->project();
        $clzz = $message::class;
        $data = JsonUtil::decode($sharedVariableCache->readString('bus_messages', '[]'));
        $key = "{$project}_{$clzz}";
        if (!in_array($key, $data, true)) {
            $data[] = $key;
        }
        sort($data);
        $sharedVariableCache->saveString('bus_messages', JsonUtil::encode($data), 60);

        // $sharedVariableCache->beginTransaction();
        $times = max($sharedVariableCache->readInt("bus_messages_{$key}") - 1, 0);
        $sharedVariableCache->saveInt("bus_messages_{$key}", $times, ttl: 60);
        // $sharedVariableCache->closeTransaction();
    }

    private function decreasePendingCounter(MessageInterface $message): void
    {
        $context = $this->service(ContextInterface::class);
        $sharedVariableCache = $this->service(SharedVariableCache::class);

        $project = $context->project();
        $clzz = $message::class;
        $data = JsonUtil::decode($sharedVariableCache->readString('bus_messages_pending', '[]'));
        $key = "{$project}_{$clzz}";
        if (!in_array($key, $data, true)) {
            $data[] = $key;
        }
        sort($data);
        $sharedVariableCache->saveString('bus_messages_pending', JsonUtil::encode($data), 60);

        // $sharedVariableCache->beginTransaction();
        $times = max($sharedVariableCache->readInt("bus_messages_pending_{$key}") - 1, 0);
        $sharedVariableCache->saveInt("bus_messages_pending_{$key}", $times, ttl: 60);
        // $sharedVariableCache->closeTransaction();
    }

    private function increaseCounter(MessageInterface $message): void
    {
        $context = $this->service(ContextInterface::class);
        $sharedVariableCache = $this->service(SharedVariableCache::class);

        $project = $context->project();
        $clzz = $message::class;
        $data = JsonUtil::decode($sharedVariableCache->readString('bus_messages', '[]'));
        $key = str_replace('\\', '_', "{$project}_{$clzz}");
        if (!in_array($key, $data, true)) {
            $data[] = $key;
        }
        sort($data);
        $sharedVariableCache->saveString('bus_messages', JsonUtil::encode($data), 60);

        $times = min(100_000, $sharedVariableCache->readInt("bus_messages_{$key}") + 1);
        $sharedVariableCache->saveInt("bus_messages_{$key}", $times, ttl: 60);
    }

    private function increasePendingCounter(MessageInterface $message): void
    {
        $context = $this->service(ContextInterface::class);
        $sharedVariableCache = $this->service(SharedVariableCache::class);

        $project = $context->project();
        $clzz = $message::class;
        $key = "{$project}_{$clzz}";
        $data = JsonUtil::decode($sharedVariableCache->readString('bus_messages_pending', '[]'));
        if (!in_array($key, $data, true)) {
            $data[] = $key;
        }
        sort($data);
        $sharedVariableCache->saveString('bus_messages_pending', JsonUtil::encode($data), 60);

        $times = min(1_000_000, $sharedVariableCache->readInt("bus_messages_pending_{$key}") + 1);
        $sharedVariableCache->saveInt("bus_messages_pending_{$key}", $times, ttl: 60);
    }

    private function normalizeClass(string $class): string
    {
        return strtolower(str_replace('\\', '_', $class));
    }
}
