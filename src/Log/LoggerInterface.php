<?php

declare(strict_types=1);

namespace Xgc\Log;

use Throwable;
use Xgc\Dto\DocumentInterface;
use Xgc\Enums\LogLevel;

interface LoggerInterface
{
    public const string DEBUG = 'DEBUG';

    public const string ERROR = 'ERROR';

    public const string INFO = 'INFO';

    public const string WARNING = 'WARNING';

    /**
     * @param mixed[] $payload
     * @param array<string, string> $labels
     */
    public function debug(
        array $payload,
        array $labels,
        string $message,
        bool $ignoreRedis = false
    ): void;

    /**
     * @param mixed[] $payload
     * @param array<string, string> $labels
     */
    public function error(array $payload, array $labels, string $message): void;

    public function exception(Throwable $throwable, ?DocumentInterface $input = null, bool $asWarning = false): void;

    /**
     * @param mixed[] $payload
     * @param array<string, string> $labels
     */
    public function info(array $payload, array $labels, string $message): void;

    /**
     * @param mixed[] $payload
     * @param array<string, string> $labels
     */
    public function log(
        LogLevel $level,
        array $payload,
        array $labels,
        string $message,
    ): void;

    public function temporalLogs(): void;

    /**
     * @param mixed[] $payload
     * @param array<string, string> $labels
     */
    public function warning(array $payload, array $labels, string $message): void;
}
