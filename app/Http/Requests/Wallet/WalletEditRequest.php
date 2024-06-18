<?php

declare(strict_types=1);

namespace App\Http\Requests\Wallet;

use App\Enums\Permissions;
use App\Enums\Wallets;
use App\Http\Requests\JsonRequest;

class WalletEditRequest extends WalletAddRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::WALLET_EDIT);
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'is_active' => 'boolean',
        ];
    }
}
