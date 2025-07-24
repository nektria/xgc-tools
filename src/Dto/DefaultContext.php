<?php

declare(strict_types=1);

namespace Xgc\Dto;

use Xgc\Utils\ContainerBoxTrait;

class DefaultContext implements ContextInterface
{
    use ContainerBoxTrait;

    /** @var mixed[] */
    private array $metadata;

    private string $traceId;

    public function __construct(
        private readonly string $env,
        private readonly string $project,
    ) {
    }

    public function env(): string
    {
        return $this->env;
    }

    /**
     * @return mixed[]
     */
    public function extras(): array
    {
        return $this->metadata;
    }

    public function isDebug(): bool
    {
        return $this->env !== 'prod';
    }

    public function isDev(): bool
    {
        return $this->env === 'dev';
    }

    public function isProd(): bool
    {
        return $this->env === 'prod';
    }

    public function isTest(): bool
    {
        return $this->env === 'test';
    }

    public function project(): string
    {
        return $this->project;
    }

    /**
     * @param mixed[] $extras
     */
    public function setMetadata(array $extras): void
    {
        $this->metadata = $extras;
    }

    public function setTraceId(string $traceId): void
    {
        $this->traceId = $traceId;
    }

    public function traceId(): string
    {
        return $this->traceId;
    }
}
