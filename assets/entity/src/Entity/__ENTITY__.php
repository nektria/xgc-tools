<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nektria\Entity\Entity;

#[ORM\Entity]
class __ENTITY__ extends Entity
{
    public function __construct(string $id)
    {
        parent::__construct($id);
    }
}
