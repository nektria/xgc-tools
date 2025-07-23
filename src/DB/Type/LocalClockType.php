<?php

declare(strict_types=1);

namespace Xgc\DB\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Xgc\Dto\LocalClock;

use function is_string;

class LocalClockType extends Type
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
            return $value->dateTimeString();
        }

        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?LocalClock
    {
        if ($value === null) {
            return null;
        }

        return LocalClock::fromString($value);
    }

    public function getName(): string
    {
        return 'local_clock';
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'TIMESTAMP(0) WITHOUT TIME ZONE';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
