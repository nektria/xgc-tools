<?php

declare(strict_types=1);

namespace Xgc\Utils;

use Throwable;
use Xgc\Dto\Clock;
use Xgc\Dto\LocalClock;
use Xgc\Dto\Metadata;
use Xgc\Exception\InvalidArgumentException;
use Xgc\Exception\MissingArgumentException;

use function count;
use function is_array;
use function is_bool;
use function is_object;
use function is_string;

readonly class ArrayDataFetcher
{
    /**
     * @param mixed[] $data
     */
    public function __construct(protected readonly array $data)
    {
    }

    /**
     * @return mixed[]
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * @return mixed[]|null
     */
    public function getArray(string $field): ?array
    {
        $value = $this->getValue($field);

        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException($field, $value, 'array');
        }

        return $value;
    }

    public function getArrayDataFetcher(string $field): ?self
    {
        $value = $this->getValue($field);

        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException($field, $value, 'array');
        }

        return new self($value);
    }

    /**
     * @return ArrayDataFetcher[]|null
     */
    public function getArrayDataFetchers(string $field): ?array
    {
        $value = $this->getValue($field);

        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException($field, $value, 'array');
        }

        $result = [];
        foreach ($value as $key => $val) {
            if (!is_array($val)) {
                throw new InvalidArgumentException("{$field}.{$key}", $value, 'array');
            }
            $result[] = new self($val);
        }

        return $result;
    }

    public function getBool(string $field): ?bool
    {
        $value = $this->getValue($field);

        if ($value === null) {
            return null;
        }

        if ($value === 'true' || $value === 'false') {
            return $value === 'true';
        }

        if (is_bool($value)) {
            return $value;
        }

        throw new InvalidArgumentException($field, $value, 'bool');
    }

    public function getClock(string $field): ?Clock
    {
        $value = $this->getValue($field);

        if ($value === null) {
            return null;
        }

        try {
            return Clock::fromString($value);
        } catch (Throwable) {
            throw new InvalidArgumentException($field, $value, 'datetime');
        }
    }

    public function getDate(string $field): ?Clock
    {
        return $this->getClock($field);
    }

    public function getFloat(string $field): ?float
    {
        $value = $this->getValue($field);

        if ($value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            throw new InvalidArgumentException($field, $value, 'float');
        }

        return (float) $value;
    }

    public function getId(string $field): ?string
    {
        $val = $this->getString($field);

        ValidateOpt::uuid4($field, $val);

        return $val;
    }

    /**
     * @return string[]|null
     */
    public function getIdsCSV(string $field): ?array
    {
        $value = $this->getString($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        if ($value === '') {
            return [];
        }

        return explode(',', $value);
    }

    public function getInt(string $field): ?int
    {
        $value = $this->getValue($field);

        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            throw new InvalidArgumentException($field, $value, 'int');
        }

        if (((string) (int) $value) !== (string) $value) {
            throw new InvalidArgumentException($field, $value, 'int');
        }

        return max(-2147483648, min((int) $value, 2147483647));
    }

    /**
     * @return int[]|null
     */
    public function getIntArray(string $field): ?array
    {
        if (!$this->hasField($field)) {
            return null;
        }

        $ret = [];
        $length = $this->retrieveLength($field);
        for ($i = 0; $i < $length; ++$i) {
            $ret[] = $this->retrieveInt("{$field}.{$i}");
        }

        return $ret;
    }

    public function getLength(string $field): int
    {
        $fieldParts = explode('.', $field);
        $currentValue = $this->data[$fieldParts[0]] ?? null;
        $length = count($fieldParts);

        if ($length === 1) {
            if ($currentValue === null) {
                return 0;
            }

            if (!is_array($currentValue)) {
                return 0;
            }

            return count($currentValue);
        }

        if (!is_array($currentValue)) {
            return 0;
        }

        for ($i = 1; $i < $length; ++$i) {
            $part = $fieldParts[$i];
            $currentValue = $currentValue[$part] ?? null;

            if (!is_array($currentValue)) {
                return 0;
            }

            if ($i === $length - 1) {
                return count($currentValue);
            }
        }

        return 0;
    }

    public function getLocalClock(string $field): ?LocalClock
    {
        $value = $this->getValue($field);

        if ($value === null) {
            return null;
        }

        try {
            return LocalClock::fromString($value);
        } catch (Throwable) {
            throw new InvalidArgumentException($field, $value, 'datetime');
        }
    }

    public function getLocalDate(string $field): ?LocalClock
    {
        return $this->getLocalClock($field);
    }

    public function getMetadata(string $field): ?Metadata
    {
        $value = $this->getArray($field);

        if ($value === null) {
            return null;
        }

        return new Metadata($value);
    }

    public function getString(string $field): ?string
    {
        $value = $this->getValue($field);

        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException($field, $value, 'string');
        }

        return StringUtil::trim($value);
    }

    /**
     * @return string[]|null
     */
    public function getStringArray(string $field): ?array
    {
        if (!$this->hasField($field)) {
            return null;
        }

        $ret = [];
        $length = $this->retrieveLength($field);
        for ($i = 0; $i < $length; ++$i) {
            $ret[] = $this->retrieveString("{$field}.{$i}");
        }

        return $ret;
    }

    public function hasField(string $field): bool
    {
        return $this->getValue($field) !== null;
    }

    /**
     * @return mixed[]
     */
    public function retrieveArray(string $field): array
    {
        $value = $this->getArray($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    public function retrieveArrayDataFetcher(string $field): self
    {
        $value = $this->getArrayDataFetcher($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    /**
     * @return ArrayDataFetcher[]
     */
    public function retrieveArrayDataFetchers(string $field): array
    {
        $value = $this->getArrayDataFetchers($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    public function retrieveBool(string $field): bool
    {
        $value = $this->getBool($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    public function retrieveClock(string $field): Clock
    {
        $value = $this->getClock($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    public function retrieveDate(string $field): Clock
    {
        return $this->retrieveClock($field);
    }

    public function retrieveFloat(string $field): float
    {
        $value = $this->getFloat($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    public function retrieveId(string $field): string
    {
        $value = $this->getId($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    /**
     * @return string[]
     */
    public function retrieveIdsCSV(string $field): array
    {
        $value = $this->getIdsCSV($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    public function retrieveInt(string $field): int
    {
        $value = $this->getInt($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    /**
     * @return int[]
     */
    public function retrieveIntArray(string $field): array
    {
        $value = $this->getIntArray($field);
        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    public function retrieveLength(string $field): int
    {
        $fieldParts = explode('.', $field);
        $currentValue = $this->data[$fieldParts[0]] ?? null;
        $length = count($fieldParts);
        $acummulative = $fieldParts[0];

        if ($length === 1) {
            if ($currentValue === null) {
                throw new MissingArgumentException($field);
            }

            if (!is_array($currentValue)) {
                throw new InvalidArgumentException($acummulative, $currentValue, 'array');
            }

            return count($currentValue);
        }

        if (!is_array($currentValue)) {
            if (is_numeric($fieldParts[1])) {
                throw new InvalidArgumentException($acummulative, $currentValue, 'array');
            }

            throw new InvalidArgumentException($acummulative, $currentValue, 'object');
        }

        for ($i = 1; $i < $length; ++$i) {
            $part = $fieldParts[$i];
            $acummulative .= ".{$part}";

            $currentValue = $currentValue[$part] ?? null;

            if ($i === $length - 1) {
                if (!is_array($currentValue)) {
                    throw new InvalidArgumentException($acummulative, $currentValue, 'array');
                }

                return count($currentValue);
            }

            if (!is_array($currentValue)) {
                if (is_numeric($fieldParts[$i + 1])) {
                    throw new InvalidArgumentException($acummulative, $currentValue, 'array');
                }

                throw new InvalidArgumentException($acummulative, $currentValue, 'object');
            }
        }

        return 0;
    }

    public function retrieveLocalClock(string $field): LocalClock
    {
        $value = $this->getLocalClock($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    public function retrieveLocalDate(string $field): LocalClock
    {
        $value = $this->getLocalClock($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    public function retrieveMetadata(string $field): Metadata
    {
        $value = $this->getMetadata($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    public function retrieveString(string $field): string
    {
        $value = $this->getString($field);

        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    /**
     * @return string[]
     */
    public function retrieveStringArray(string $field): array
    {
        $value = $this->getStringArray($field);
        if ($value === null) {
            throw new MissingArgumentException($field);
        }

        return $value;
    }

    protected function getValue(string $field): mixed
    {
        if (!str_contains($field, '.')) {
            $value = $this->data[$field] ?? null;

            if ($value === null) {
                return null;
            }

            if (is_string($value)) {
                $value = StringUtil::trim($value);
            }

            if ($value === 'null') {
                return null;
            }

            if ($value === 'true') {
                return true;
            }

            if ($value === 'false') {
                return false;
            }

            return $value;
        }

        $fieldParts = explode('.', $field);
        $currentValue = $this->data[$fieldParts[0]] ?? null;
        $length = count($fieldParts);

        if (!is_array($currentValue)) {
            return null;
        }

        for ($i = 1; $i < $length; ++$i) {
            $part = $fieldParts[$i];

            if ($i === ($length - 1)) {
                $value = $currentValue[$part] ?? null;

                if ($value === null) {
                    return null;
                }

                if (is_string($value)) {
                    $value = StringUtil::trim($value);
                }

                if ($value === 'null') {
                    return null;
                }

                if ($value === 'true') {
                    return true;
                }

                if ($value === 'false') {
                    return false;
                }

                return $value;
            }

            $currentValue = $currentValue[$part] ?? null;

            if (!is_array($currentValue)) {
                return null;
            }
        }

        return null;
    }
}
