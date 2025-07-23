<?php

declare(strict_types=1);

namespace Xgc\DB;

enum PersistenceType
{
    case HardUpdate;
    case New;
    case None;
    case SoftUpdate;
}
