<?php

declare(strict_types=1);

namespace Xgc\DB\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Xgc\Dto\LocalClock;

use function is_string;

class LocalClockDateType extends Type
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof LocalClock) {
            return $value->dateString();
        }

        return null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?LocalClock
    {
        if (!is_string($value)) {
            return null;
        }

        return LocalClock::fromString($value);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'DATE';
    }

    public function getName(): string
    {
        return 'local_clock_date';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
