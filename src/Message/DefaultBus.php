<?php

declare(strict_types=1);

namespace Xgc\Message;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Throwable;
use Xgc\Dto\ContextInterface;
use Xgc\Dto\DocumentInterface;
use Xgc\Exception\BaseException;
use Xgc\Symfony\Service\ContainerAwareServiceTrait;

readonly class DefaultBus implements BusInterface
{
    use ContainerAwareServiceTrait;

    public function __construct()
    {
        $this->initContainerAwareServiceTrait();
    }

    public function dispatchCommand(
        Command $command,
        ?string $transport = null,
        ?DelayStamp $delayMs = null,
        ?RetryStamp $retryOptions = null
    ): void {
        $context = $this->service(ContextInterface::class);

        $stamps = [
            new ContextStamp(
                traceId: $context->traceId(),
                context: '',
                data: $context->extras()
            ),
        ];

        if ($transport !== null) {
            $stamps[] = new TransportNamesStamp([$transport]);
        }

        if ($retryOptions !== null) {
            $stamps[] = $retryOptions;
        }

        if ($delayMs !== null) {
            $stamps[] = $delayMs;
        }

        $bus = $this->service(MessageBusInterface::class);

        try {
            $bus->dispatch($command, $stamps);
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public function dispatchEvent(
        Event $command,
        ?string $transport = null,
        ?DelayStamp $delayMs = null,
        ?RetryStamp $retryOptions = null
    ): void {
        $context = $this->service(ContextInterface::class);
        $bus = $this->service(MessageBusInterface::class);

        $stamps = [
            new ContextStamp(
                traceId: $context->traceId(),
                context: '',
                data: $context->extras()
            ),
        ];

        if ($transport !== null) {
            $stamps[] = new TransportNamesStamp([$transport]);
        }

        if ($retryOptions !== null) {
            $stamps[] = $retryOptions;
        }

        if ($delayMs !== null) {
            $stamps[] = $delayMs;
        }

        try {
            $bus->dispatch($command, $stamps);
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    /**
     * @template T of DocumentInterface
     * @param Query<T> $query
     * @return T
     */
    public function dispatchQuery(Query $query): DocumentInterface
    {
        $context = $this->service(ContextInterface::class);
        $bus = $this->service(MessageBusInterface::class);

        $stamps = [
            new ContextStamp(
                traceId: $context->traceId(),
                context: '',
                data: $context->extras()
            ),
        ];

        try {
            $result = $bus->dispatch($query, $stamps)->last(HandledStamp::class);
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }

        if ($result === null) {
            throw new BaseException('Query does not return a result');
        }

        /** @var T $document */
        $document = $result->getResult();

        return $document;
    }
}
