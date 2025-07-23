<?php

declare(strict_types=1);

namespace Xgc\Log;

use Xgc\Dto\MutableMetadata;

readonly class ProcessRegistry
{
    /**
     * @var MutableMetadata<string>
     */
    private MutableMetadata $metadata;

    public function __construct()
    {
        $this->metadata = new MutableMetadata();
    }

    public function addValue(string $key, string $value): void
    {
        $this->metadata->updateField($key, $value);
    }

    public function clear(): void
    {
        $this->metadata->clear();
    }

    /**
     * @return  MutableMetadata<string>
     */
    public function getMetadata(): MutableMetadata
    {
        return $this->metadata;
    }
}
