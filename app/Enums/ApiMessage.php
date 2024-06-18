<?php

declare(strict_types=1);

namespace App\Enums;

enum ApiMessage: string
{
    case OTP_ENABLED_SUCCESS = 'Two factor authentication enabled successfully';
    case OTP_DISABLED_SUCCESS = 'Two factor authentication disabled successfully';
    case EMAIL_VERIFIED_SUCCESS = 'Email verified successfully';
    case EMAIL_VERIFICATION_LINK_SENT = 'Email verification link sent successfully';
    case PASSWORD_CHANGED_SUCCESS = 'Password changed successfully';
    case LOGGED_OUT_SUCCESS = 'Logged out successfully';
    case AVATAR_CHANGED_SUCCESS = 'Avatar changed successfully';
    case KYC_DOCUMENTS_SUBMITTED_SUCCESS = 'KYC documents submitted successfully';
    case PASSWORD_RESET_LINK_SENT = 'Password reset link sent successfully';
    case PASSWORD_RESET_SUCCESS = 'Password reset successfully';
}
