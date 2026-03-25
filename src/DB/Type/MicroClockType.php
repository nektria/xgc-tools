<?php

declare(strict_types=1);

namespace Xgc\DB\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Xgc\Dto\Clock;

use function is_string;

class MicroClockType extends Type
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Clock) {
            return $value->microDateTimeString();
        }

        return null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Clock
    {
        if (!is_string($value)) {
            return null;
        }

        return Clock::fromString($value);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'TIMESTAMP(6) WITHOUT TIME ZONE';
    }

    public function getName(): string
    {
        return 'micro_clock';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
