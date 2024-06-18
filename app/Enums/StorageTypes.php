<?php

declare(strict_types=1);

namespace App\Enums;

enum StorageTypes: string
{
    case KYC = 'kyc';
    case AVATAR = 'avatars';
    case MESSAGE = 'messages';
}
