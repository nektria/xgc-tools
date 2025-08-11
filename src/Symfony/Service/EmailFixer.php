<?php

declare(strict_types=1);

namespace Xgc\Symfony\Service;

use Xgc\Utils\StringUtil;

class EmailFixer
{
    private bool $fixEmail;

    public function __construct(
        string $fixEmail
    ) {
        $this->fixEmail = $fixEmail === 'true';
    }

    public function fix(string $email): string
    {
        if (!$this->fixEmail) {
            return $email;
        }

        return StringUtil::fixEmail($email);
    }
}
