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
        public int $limit,
    ) {
    }

    public function toArray(?ContextInterface $context = null): array
    {
        return [
            'limit' => $this->limit,
            'list' => $this->list->toArray($context),
            'offset' => $this->offset,
            'total' => $this->total,
        ];
    }
}
