<?php

declare(strict_types=1);

namespace Xgc\Dto;

/**
 * @template T
 */
class MutableMetadata
{
    /** @var array<string, T> */
    private array $data;

    /**
     * @param array<string, T> $data
     */
    final public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * @return array<string, T>
     */
    public function data(): array
    {
        return $this->data;
    }

    public function getField(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    /**
     * @param MutableMetadata<T> $metadata
     */
    public function merge(self $metadata): void
    {
        $this->mergeData($metadata->data());
    }

    /**
     * @param array<string, T> $data
     */
    public function mergeData(array $data): void
    {
        $this->data = [...$this->data, ...$data];
    }

    /**
     * @param T $value
     */
    public function updateField(string $field, mixed $value): void
    {
        $this->data[$field] = $value;
    }
}
