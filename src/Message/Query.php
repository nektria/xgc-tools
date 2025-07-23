<?php

declare(strict_types=1);

namespace Xgc\Message;

use Xgc\Dto\DocumentInterface;

/**
 * @template T of DocumentInterface
 */
interface Query extends MessageInterface
{
}
