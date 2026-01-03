<?php

declare(strict_types=1);

namespace Xgc\Symfony\Service;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Throwable;
use Xgc\Dto\Document;
use Xgc\Exception\BaseException;
use Xgc\Utils\JsonUtil;

readonly class WSService
{
    public function __construct(
        private HubInterface $hub,
        private string $mercureHost,
        private string $mercureToken,
    ) {
    }

    public function publish(string $topic, Document $data): void
    {
        if ($this->mercureToken === 'none' || $this->mercureHost === 'none') {
            return;
        }

        try {
            $this->hub->publish(new Update("/{$topic}", JsonUtil::encode([
                'payload' => $data->toArray(),
            ]), true));
        } catch (Throwable $e) {
            throw BaseException::extend($e);
        }
    }
}
