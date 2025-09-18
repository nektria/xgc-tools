<?php

declare(strict_types=1);

namespace Xgc\Symfony\Controller;

use Symfony\Component\HttpFoundation\Response;

class WebResponse extends Response
{
    /**
     * @param mixed[] $parameters
     */
    public function __construct(
        string $content,
        public readonly int $status = 200,
        public readonly array $parameters = [],
    ) {
        parent::__construct($content);
    }
}
