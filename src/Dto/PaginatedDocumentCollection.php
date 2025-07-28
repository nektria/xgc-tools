<?php

declare(strict_types=1);

namespace Xgc\Dto;

/**
 * @template T of Document
 */
readonly class PaginatedDocumentCollection extends Document
{
    /**
     * @param DocumentCollection<T> $list
     */
    public function __construct(
        public DocumentCollection $list,
        public int $offset,
        public int $total,
    ) {
    }

    public function toArray(?ContextInterface $context = null): array
    {
        return [
            'list' => $this->list->toArray($context),
            'offset' => $this->offset,
            'total' => $this->total,
        ];
    }
}
