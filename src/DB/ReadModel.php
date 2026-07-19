<?php

declare(strict_types=1);

namespace Xgc\DB;

use Doctrine\ORM\EntityManagerInterface;
use Throwable;
use Xgc\Dto\Document;
use Xgc\Dto\DocumentCollection;
use Xgc\Dto\PaginatedDocumentCollection;
use Xgc\Exception\BaseException;
use Xgc\Utils\StringUtil;

use function count;
use function is_array;

/**
 * @template T of Document
 */
abstract class ReadModel
{
    public const int MAX_RESULTS = 1024;

    private static int $defaultPageSize = 100;

    public function __construct(
        protected readonly EntityManagerInterface $manager,
    ) {}

    public static function setDefaultPageSize(int $pageSize): void
    {
        self::$defaultPageSize = $pageSize;
    }

    /**
     * @param array<string, scalar|null> $params
     * @return T
     */
    protected function buildDocument(array $params): Document
    {
        throw new BaseException(
            'source() should be implemented in the child class when using getResult(), ' .
            'getResults() or getPaginatedResult().',
        );
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @param array<string, 'ASC'|'DESC'> $orderBy
     * @return DocumentCollection<T>
     */
    protected function getNewResults(
        string $sql,
        array $params = [],
        array $orderBy = [],
        ?int $limit = null,
    ): DocumentCollection {
        $sql = $this->buildSQL($sql, $params, $orderBy, null, $limit, false);
        $results = $this->getRawResults($sql, $params);
        $parsed = [];

        foreach ($results as $item) {
            $parsed[] = $this->buildDocument($item);
        }

        return new DocumentCollection($parsed);
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @param array<string, 'ASC'|'DESC'> $orderBy
     * @return PaginatedDocumentCollection<T>
     */
    protected function getPaginatedResults(
        string $sql,
        array $orderBy,
        ?int $page = null,
        ?int $limit = null,
        array $params = [],
    ): PaginatedDocumentCollection {
        $page ??= 1;
        $limit ??= self::$defaultPageSize;
        $sql = $this->buildSQL($sql, $params, $orderBy, $page, $limit, true);
        $results = $this->getRawResults($sql, $params);
        $parsed = [];

        foreach ($results as $item) {
            $parsed[] = $this->buildDocument($item);
        }

        return new PaginatedDocumentCollection(
            new DocumentCollection($parsed),
            $page,
            $limit,
            (int) ($results[0]['__total__'] ?? 0),
        );
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @return array<string, scalar|null>|null
     */
    protected function getRawResult(string $sql, array $params = []): ?array
    {
        try {
            $results = $this->getRawResults($sql, $params);

            return $results[array_key_first($results) ?? ''] ?? null;
        } catch (Throwable $e) {
            BaseException::extendAndThrow($e);
        }
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @return array<int, array<string, scalar|null>>
     */
    protected function getRawResults(string $sql, array $params = []): array
    {
        $newParams = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = "'" . implode("','", $value) . "'";
                $sql = str_replace(":$key", $value, $sql);
            } else {
                $newParams[$key] = $value;
            }
        }

        try {
            $query = $this->manager->getConnection()->prepare($sql);
            foreach ($newParams as $key => $value) {
                $query->bindValue($key, $value);
            }

            /** @var array<int, array<string, scalar|null>> $data */
            $data = $query->executeQuery()->fetchAllAssociative();

            return $data;
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @param array<string, 'ASC'|'DESC'> $orderBy
     * @return T|null
     */
    protected function getResult(string $sql, array $params = [], array $orderBy = []): ?Document
    {
        $sql = $this->buildSQL($sql, $params, $orderBy, null, 1, false);
        $result = $this->getRawResult($sql, $params);

        if ($result === null) {
            return null;
        }

        return $this->buildDocument($result);
    }

    protected function source(): string
    {
        throw new BaseException(
            'source() should be implemented in the child class when using getResult(), ' .
            'getResults() or getPaginatedResult().',
        );
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @param array<string, 'ASC'|'DESC'> $orderBy
     */
    private function buildSQL(
        string $where,
        array &$params,
        array $orderBy,
        ?int $page,
        ?int $limit,
        bool $pagination,
    ): string {
        $source = $this->source();

        $ob = '';
        if (count($orderBy) > 0) {
            $ob = 'ORDER BY ';
            foreach ($orderBy as $key => $value) {
                $ob .= "{$key} {$value}, ";
            }
            $ob = rtrim($ob, ', ');
        }

        if (str_contains($source, '::QUERY::')) {
            $source = str_replace('::QUERY::', $where, $source);
        } else {
            $source = "{$source} {$where}";
        }

        $l = '';
        $o = '';
        if ($limit !== null) {
            $l = 'LIMIT :__limit__';
            $params['__limit__'] = $limit === 9999 ? 9999 : max(1, min(self::MAX_RESULTS, $limit));
            if ($pagination) {
                $sources = explode('FROM', $source);
                $source = "{$sources[0]}, COUNT(*) OVER() AS __total__ FROM {$sources[1]}";
            }

            if ($page !== null) {
                $offset = max(0, ($page - 1) * $limit);
                $o = 'OFFSET :__offset__';
                $params['__offset__'] = $offset;
            }
        } elseif (!str_contains($source, 'LIMIT')) {
            $l = 'LIMIT :__limit__';
            $params['__limit__'] = max(1, min(self::MAX_RESULTS, self::$defaultPageSize));
        }

        return StringUtil::trim("{$source} {$ob} {$l} {$o}");
    }
}
