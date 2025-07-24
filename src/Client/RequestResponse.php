<?php

declare(strict_types=1);

namespace Xgc\Client;

use Throwable;
use Xgc\Exception\BaseException;
use Xgc\Utils\JsonUtil;

readonly class RequestResponse
{
    /**
     * @param array<string, string|int|bool> $requestHeaders
     * @param array<string, (string|int|bool)[]> $responseHeaders
     * @param array<string, string> $cookies
     */
    public function __construct(
        public string $method,
        public string $url,
        public int $status,
        public string $body,
        public array $requestHeaders,
        public array $responseHeaders,
        public array $cookies,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->status < 400;
    }

    public function json(): mixed
    {
        try {
            return JsonUtil::decode($this->body);
        } catch (BaseException) {
            return [
                '_response' => $this->body,
            ];
        } catch (Throwable $e) {
            return [
                '_response' => $e->getMessage(),
            ];
        }
    }
}
