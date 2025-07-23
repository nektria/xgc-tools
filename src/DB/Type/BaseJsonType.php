<?php

declare(strict_types=1);

namespace Xgc\DB\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType as DoctrineJsonType;

/**
 * @template T
 */
abstract class BaseJsonType extends DoctrineJsonType
{
    /**
     * @param T|null $value
     * @return ($value is null ? null : string)
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $this->convertToDatabase($value);
    }

    /**
     * @param T|null $value
     * @return T|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->convertToPhp($value);
    }

    public function getName(): string
    {
        return $this->getTypeName();
    }

    /**
     * @param mixed[] $column
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'JSONB';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /**
     * @param T $phpValue
     */
    abstract protected function convertToDatabase($phpValue): string;

    /**
     * @return T
     */
    abstract protected function convertToPhp(string $databaseValue);

    abstract protected function getTypeName(): string;
}
