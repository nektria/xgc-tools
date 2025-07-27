<?php

declare(strict_types=1);

namespace Xgc\Dto;

interface ContextInterface
{
    public function env(): string;

    /**
     * @return mixed[]
     */
    public function extras(): array;

    public function isDebug(): bool;

    public function isDev(): bool;

    public function isProd(): bool;

    public function isTest(): bool;

    public function project(): string;

    /**
     * @param mixed[] $extras
     */
    public function setExtras(array $extras): void;

    public function setTraceId(string $traceId): void;

    public function traceId(): string;
}
