<?php

declare(strict_types=1);

namespace Xgc\DB\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Xgc\Dto\Clock;

use function is_string;

class ClockDateType extends Type
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof Clock) {
            return $value->dateString();
        }

        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Clock
    {
        if ($value === null) {
            return null;
        }

        return Clock::fromString($value);
    }

    public function getName(): string
    {
        return 'clock_date';
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'DATE';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
