<?php

declare(strict_types=1);

namespace App\Enums;

enum EventTypes: string
{
    case SIGNED_UP = 'signed_up';
    case LOGGED_IN = 'logged_in';
    case LOGGED_OUT = 'logged_out';
    case EMAIL_VERIFIED = 'email_verified';
    case PASSWORD_CHANGED = 'password_changed';
    case AVATAR_CHANGED = 'avatar_changed';
    case REQUESTED_2FA_SETUP = '2fa_requested_setup';
    case REQUESTED_2FA_SETUP_SUCCESS = '2fa_setup_success';
    case REQUESTED_2FA_SETUP_FAILURE = '2fa_setup_failure';
    case REQUESTED_2FA_DISABLE = '2fa_disable';
    case EMAIL_CHANGED = 'email_changed';
    case DISABLED_2FA = '2fa_disabled';
    case WITHDRAWAL_REQUESTED = 'withdrawal_requested';
    case DEPOSIT_ADDED = 'deposit_added';
    case DEPOSIT_EDITED = 'deposit_edited';
    case WITHDRAWAL_COMPLETED = 'withdrawal_completed';
    case WITHDRAWAL_PRECOMPLETED = 'withdrawal_precompleted';
    case DEPOSIT_CHECKED = 'deposit_checked';
    case DEPOSIT_PRECOMPLETED = 'deposit-precompleted';
    case DEPOSIT_COMPLETED = 'deposit-completed';
    case BALANCE_CHANGED = 'balance-changed';
    case PASSWORD_RESET = 'password_reset';
}
