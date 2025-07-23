<?php

declare(strict_types=1);

namespace Xgc\Dto;

interface ContextInterface
{
    public function asyncDisabled(): void;

    public function disableAsync(): void;

    public function env(): string;

    public function isDebug(): bool;

    public function isDev(): bool;

    public function isProd(): bool;

    public function isTest(): bool;

    /**
     * @return MutableMetadata<string>
     */
    public function metadata(): MutableMetadata;

    public function project(): string;

    /**
     * @param MutableMetadata<string> $metadata
     */
    public function setMetadata(MutableMetadata $metadata): void;

    public function setTraceId(string $traceId): void;

    public function traceId(): string;
}
