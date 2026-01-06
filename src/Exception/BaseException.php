<?php

declare(strict_types=1);

namespace Xgc\Exception;

use RuntimeException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Throwable;

class BaseException extends RuntimeException
{
    public readonly bool $convertToAlert;

    public readonly bool $convertToLog;

    public readonly string $hash;

    public readonly bool $retryWhenAsync;

    /**
     * @param array<string, mixed> $extras
     * @param array{
     *     convertToAlert?: bool,
     *     convertToLog?: bool,
     *     retryWhenAsync?: bool,
     *     hash?: string,
     * } $options
     */
    public function __construct(
        string $message,
        public readonly int $status = 500,
        public readonly ?array $extras = null,
        public readonly array $options = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $status, $previous);

        $this->convertToAlert = $this->options['convertToAlert'] ?? true;
        $this->convertToLog = $this->options['convertToLog'] ?? true;
        $this->retryWhenAsync = $this->options['retryWhenAsync'] ?? true;

        $this->hash = md5($this->options['hash'] ?? $message);
    }

    /** @noinspection SelfClassReferencingInspection */
    public static function extend(Throwable $e): self
    {
        while ($e instanceof HandlerFailedException) {
            $tmp = $e->getPrevious();
            if ($tmp === null) {
                break;
            }
            $e = $tmp;
        }

        if ($e instanceof self) {
            return $e;
        }

        return new self($e->getMessage(), extras: [], previous: $e);
    }

    public static function extendAndThrow(Throwable $e): self
    {
        try {
            throw self::extend($e);
        } catch (BaseException $newException) {
            return $newException;
        }
    }
}
