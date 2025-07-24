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
use Xgc\Utils\ContainerBoxTrait;

readonly class DefaultBus implements BusInterface
{
    use ContainerBoxTrait;

    public function dispatchCommand(
        Command $command,
        ?string $transport = null,
        ?DelayStamp $delayMs = null,
        ?RetryStamp $retryOptions = null
    ): void {
        $context = $this->get(ContextInterface::class);

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

        $bus = $this->get(MessageBusInterface::class);

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
        $context = $this->get(ContextInterface::class);
        $bus = $this->get(MessageBusInterface::class);

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
        $context = $this->get(ContextInterface::class);
        $bus = $this->get(MessageBusInterface::class);

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
