<?php

declare(strict_types=1);

namespace Xgc\Utils;

use JsonException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Throwable;
use Xgc\Exception\BaseException;
use Xgc\Message\MessageInterface;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

class JsonUtil
{
    /**
     * @return mixed[]
     */
    public static function decode(string $data): array
    {
        try {
            /** @var mixed[] $res */
            $res = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

            return $res;
        } catch (JsonException $e) {
            throw BaseException::extend($e);
        }
    }

    public static function deserializeMessage(MessageInterface $data): mixed
    {
        try {
            $encoders = [new JsonEncoder()];
            $normalizers = [new PropertyNormalizer(), new DateTimeNormalizer(), new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);

            return self::decode($serializer->serialize($data, 'json'));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }

    public static function encode(mixed $data, bool $pretty = false): string
    {
        $prettyFlag = $pretty ? JSON_PRETTY_PRINT : 0;

        try {
            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR | $prettyFlag);
        } catch (JsonException $e) {
            throw BaseException::extend($e);
        }
    }

    public static function file(string $file): mixed
    {
        return self::decode(FileUtil::read($file));
    }
}
