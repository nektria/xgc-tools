<?php

declare(strict_types=1);

namespace Xgc\DB;

use Doctrine\ORM\Mapping as ORM;
use Xgc\Dto\Clock;

abstract class EventEntity implements EntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'micro_clock')]
    public protected(set) Clock $timestamp;

    public function __construct()
    {
        $this->timestamp = Clock::now();
    }

    public function fixTimeStamp(): void
    {
        usleep(1);
        $this->timestamp = Clock::now();
    }

    public function id(): string
    {
        return (string) $this->timestamp;
    }

    public function refresh(): void
    {
    }
}
