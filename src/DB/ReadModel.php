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
     * @param mixed[] $params
     * @return T
     */
    abstract protected function buildDocument(array $params): Document;

    /**
     * @param array<string, string|int|float|bool|null> $params
     * @param string[] $groupBy
     * @return mixed[]|null
     */
    protected function getRawResult(string $sql, array $params = [], array $groupBy = []): ?array
    {
        try {
            $results = $this->getRawResults($sql, $params, $groupBy);

            return $results[array_key_first($results)] ?? null;
        } catch (Throwable $e) {
            throw BaseException::extends($e);
        }
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @param string[] $groupBy
     * @return mixed[]
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
            $results = $query->executeQuery()->fetchAllAssociative();

            if (count($groupBy) > 0) {
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
            throw BaseException::extends($e);
        }
    }

    /**
     * @param array<string, string|int|float|bool|null> $params
     * @return T|null
     */
    protected function getResult(string $sql, array $params = []): ?Document
    {
        $result = $this->getRawResult($sql, $params, $this->groupResults());

        if ($result === null) {
            return null;
        }

        return $this->buildDocument($result);
    }

    /**
     * @param array<string, string|int|float|bool|string[]|null> $params
     * @return DocumentCollection<T>
     */
    protected function getResults(string $sql, array $params = []): DocumentCollection
    {
        $results = $this->getRawResults($sql, $params, $this->groupResults());
        $parsed = [];

        foreach ($results as $item) {
            $parsed[] = $this->buildDocument($item);
        }

        return new DocumentCollection($parsed);
    }

    abstract protected function source(): string;
}
