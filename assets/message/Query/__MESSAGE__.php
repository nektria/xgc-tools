<?php

declare(strict_types=1);

namespace App\Message\__ENTITY__;

use App\Document\__ENTITY__;
use Xgc\Message\Query;
use Xgc\Utils\Validate;

/**
 * @implements Query<__ENTITY__>
 */
readonly class __MESSAGE__ implements Query
{
    public function __construct(
        public string $__ENTITY_CC__Id,
    ) {
        Validate::uuid4($__ENTITY_CC__Id);
    }
}
