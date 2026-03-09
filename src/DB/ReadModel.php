<?php

declare(strict_types=1);

namespace Xgc\DB;

use Doctrine\ORM\EntityManagerInterface;
use Throwable;
use Xgc\Dto\Document;
use Xgc\Dto\DocumentCollection;
use Xgc\Exception\BaseException;
use Xgc\Utils\StringUtil;

use function count;
use function is_array;

/**
 * @template T of Document
 */
abstract class ReadModel
{
    protected private(set) EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return string[]
     */
    public function groupResults(): array
    {
        return [];
    }

    /**
     * @param array<string, string|int|float|bool|null> $params
     * @return T
     */
    protected function buildDocument(array $params): Document
    {
        throw new BaseException('`buildDocument` Not implemented');
    }

    /**
     * @param array<string, string|int|float|bool|null>[] $params
     * @return T
     */
    protected function buildGroupedDocument(array $params): Document
    {
        throw new BaseException('`buildGroupedDocument` Not implemented');
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @param string[] $groupBy
     * @return ($groupBy is non-empty-array
     *             ? array<string, string|int|float|bool|null>[]
     *             : array<string, string|int|float|bool|null>
     *         )|null
     */
    protected function getRawResult(string $sql, array $params = [], array $groupBy = []): ?array
    {
        try {
            $results = $this->getRawResults($sql, $params, $groupBy);

            /* @var array<string, string|int|float|bool|null>|null */
            return $results[array_key_first($results) ?? ''] ?? null;
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @param string[] $groupBy
     * @return array<string, string|int|float|bool|null>[]
     * @return ($groupBy is non-empty-array
     *              ? array<string, array<int, array<string, string|int|float|bool|null>>>
     *              : array<int, array<string, string|int|float|bool|null>>
     *         )
     */
    protected function getRawResults(string $sql, array $params = [], array $groupBy = []): array
    {
        $sql = StringUtil::trim($sql);
        if (!str_starts_with($sql, 'SELECT')) {
            $sql = "{$this->source()} {$sql}";
        }

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

            /** @var array<int, array<string, string|int|float|bool|null>> $results */
            $results = $query->executeQuery()->fetchAllAssociative();

            if (count($groupBy) > 0) {
                /** @var array<string, array<int, array<string, string|int|float|bool|null>>> $newResults */
                $newResults = [];
                foreach ($results as $item) {
                    $key = '';
                    foreach ($groupBy as $group) {
                        $key .= $item[$group];
                    }

                    $newResults[$key] ??= [];
                    $newResults[$key][] = $item;
                }

                $results = $newResults;
            }

            return $results;
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @return T|null
     */
    protected function getResult(string $sql, array $params = []): ?Document
    {
        $groups = $this->groupResults();
        $result = $this->getRawResult($sql, $params, $groups);

        if ($result === null) {
            return null;
        }

        if (count($groups) > 0) {
            /** @var array<string, string|int|float|bool|null>[] $tmp */
            $tmp = $result;

            return $this->buildGroupedDocument($tmp);
        }

        /** @var array<string, string|int|float|bool|null> $tmp2 */
        $tmp2 = $result;

        return $this->buildDocument($tmp2);
    }

    /**
     * @param array<string, string|int|float|bool|null> $params
     * @return DocumentCollection<T>
     */
    protected function getResults(string $sql, array $params = []): DocumentCollection
    {
        $groups = $this->groupResults();
        $results = $this->getRawResults($sql, $params, $groups);
        $parsed = [];

        if (count($groups) > 0) {
            /** @var array<string, array<int, array<string, string|int|float|bool|null>>> $results */
            foreach ($results as $item) {
                $parsed[] = $this->buildGroupedDocument($item);
            }
        } else {
            /** @var array<int, array<string, string|int|float|bool|null>> $results */
            foreach ($results as $item) {
                $parsed[] = $this->buildDocument($item);
            }
        }

        return new DocumentCollection($parsed);
    }

    abstract protected function source(): string;
}
