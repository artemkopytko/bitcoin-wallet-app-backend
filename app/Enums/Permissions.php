<?php

declare(strict_types=1);

namespace App\Enums;

enum Permissions: string
{
    case USER_CREATE = 'create-user';
    case USER_EDIT = 'update-user';
    case USER_DELETE = 'delete-user';
    case USER_READ = 'read-user';
    case USER_READ_SELF = 'read-user-self';
    case USER_EDIT_SELF = 'update-self';

    // Wallet
    case WALLET_READ = 'read-wallet';
    case WALLET_ADD = 'add-wallet';
    case WALLET_EDIT = 'update-wallet';
    case WALLET_DELETE = 'delete-wallet';

    // Withdrawal
    case WITHDRAWAL_READ = 'read-withdrawal';
    case WITHDRAWAL_ADD = 'add-withdrawal';
    case WITHDRAWAL_EDIT = 'update-withdrawal';
    case WITHDRAWAL_DELETE = 'delete-withdrawal';

    // Deposit
    case DEPOSIT_READ = 'read-deposit';
    case DEPOSIT_ADD = 'add-deposit';
    case DEPOSIT_EDIT = 'update-deposit';
    case DEPOSIT_DELETE = 'delete-deposit';

    // Event
    case EVENT_READ = 'read-event';

    // Statistics
    case STATISTICS_READ = 'read-statistics';
}
