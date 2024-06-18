<?php

declare(strict_types=1);

namespace App\Http\Requests\Wallet;

use App\Enums\Permissions;
use App\Enums\Wallets;
use App\Http\Requests\JsonRequest;

class WalletReadRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::WALLET_READ);
    }

    public function rules(): array
    {
        $types = array_column(Wallets::cases(), 'value');

        return [
            'type' => 'nullable|string|in:' . implode(',', $types),
            'is_active' => 'nullable|boolean',
            'search' => 'nullable|string',
        ];
    }
}
