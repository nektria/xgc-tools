<?php

declare(strict_types=1);

namespace Xgc\Message;

interface Event extends MessageInterface
{
    public function ref(): string;
}
