<?php

declare(strict_types=1);

namespace Xgc\Message;

interface Command extends MessageInterface
{
    public function ref(): string;
}
