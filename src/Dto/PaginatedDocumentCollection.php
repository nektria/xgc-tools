<?php

declare(strict_types=1);

namespace Xgc\Dto;

/**
 * @template T of Document
 */
readonly class PaginatedDocumentCollection extends Document
{
    public int $pageSize;

    /**
     * @param DocumentCollection<T> $items
     */
    public function __construct(
        public DocumentCollection $items,
        public int $page,
        public int $totalPages,
        public int $total,
    ) {
        $this->pageSize = $items->count();
    }

    public function toArray(?ContextInterface $context = null): array
    {
        return [
            'pageSize' => $this->pageSize,
            'items' => $this->items->toArray($context),
            'page' => $this->page,
            'total' => $this->total,
            'totalPages' => $this->totalPages,
        ];
    }
}
