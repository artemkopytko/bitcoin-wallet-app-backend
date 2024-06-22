<?php

declare(strict_types=1);

namespace App\Enums;

enum WithdrawalMethods: int
{
    case SWIFT = 0;
    case VISA_OR_MC = 1;
    case BTC = 2;
    case ETH = 3;
    case BNB = 4;
    case USDT_TRC_20 = 5;
    case BANK_TRANSFER_MIR = 6;
}
