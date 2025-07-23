<?php

declare(strict_types=1);

namespace Xgc\DB;

use Doctrine\ORM\Mapping as ORM;
use Xgc\Dto\Clock;
use Xgc\Utils\StringUtil;

abstract class Entity implements EntityInterface
{
    #[IgnoreProperty]
    #[ORM\Column(type: 'clock')]
    public protected(set) Clock $createdAt;

    #[IgnoreProperty]
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    public protected(set) string $id;

    #[IgnoreProperty]
    #[ORM\Column(type: 'clock')]
    public protected(set) Clock $updatedAt;

    public function __construct(?string $id = null)
    {
        $this->id = $id ?? StringUtil::uuid4();
        $this->createdAt = Clock::now();
        $this->updatedAt = $this->createdAt;
    }

    public function refresh(): void
    {
        $this->updatedAt = Clock::now();
    }
}
