<?php

declare(strict_types=1);

namespace App\Enums;

enum ApiError: string
{
    case OTP_INVALID_CODE = 'Invalid one time password code.';
    case OTP_NOT_ENABLED = '2FA is not enabled for. Please request the OTP creation first.';
    case OTP_ALREADY_ENABLED = '2FA is already enabled.';
    case OTP_ALREADY_DISABLED = '2FA is already disabled.';
    case USER_NOT_FOUND = 'User not found.';
    case USER_ALREADY_EXISTS = 'User already exists.';
    case EMAIL_CONFIRMATION_LINK_EXPIRED = 'Email confirmation link expired.';
    case EMAIL_CONFIRMATION_HASH_MISMATCH = 'Email confirmation link invalid.';
    case EMAIL_ALREADY_VERIFIED = 'Email already verified.';
    case RECAPTCHA_ERROR = 'Recaptcha error.';
    case WRONG_PASSWORD = 'Wrong password.';
    case UNAUTHORIZED = 'Unauthorized.';
    case USER_NOT_ACTIVE = 'Your account is not active. Please contact the administrator.';
    case USER_KYC_ALREADY_VERIFIED = 'You are already KYC verified';
    case USER_KYC_PENDING = 'Your KYC verification still pending. Please wait';
    case INVALID_RESET_TOKEN = 'Invalid reset token.';
}
