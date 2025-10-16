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
     * @param DocumentCollection<T> $list
     */
    public function __construct(
        public DocumentCollection $list,
        public int $page,
        public int $totalPages,
        public int $total,
    ) {
        $this->pageSize = $list->count();
    }

    public function toArray(?ContextInterface $context = null): array
    {
        return [
            'pageSize' => $this->pageSize,
            'list' => $this->list->toArray($context),
            'page' => $this->page,
            'total' => $this->total,
            'totalPages' => $this->totalPages,
        ];
    }
}
