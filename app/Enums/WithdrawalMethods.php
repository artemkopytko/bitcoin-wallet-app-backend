<?php

declare(strict_types=1);

namespace App\Enums;

enum WithdrawalMethods: int
{
    case BANK = 0;
    case CRYPTO = 1;
    case PAYPAL = 2;
    case SKRILL = 3;
    case OTHER = 4;
    case BTC = 5;
    case ETH = 6;
    case BNB = 7;
    case USDT_TRC_20 = 8;
}
