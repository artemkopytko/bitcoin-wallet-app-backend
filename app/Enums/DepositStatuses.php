<?php

declare(strict_types=1);

namespace App\Enums;

enum DepositStatuses: int
{
    case NEW = 0;
    case PENDING = 1;
    case APPROVED = 2;
    case DECLINED = 3;
    case PROCESSING = 4;
    case COMPLETED = 5;
}
