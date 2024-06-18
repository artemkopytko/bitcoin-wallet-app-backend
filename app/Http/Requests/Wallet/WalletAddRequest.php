<?php

declare(strict_types=1);

namespace App\Http\Requests\Wallet;

use App\Enums\Permissions;
use App\Enums\Wallets;
use App\Http\Requests\JsonRequest;

class WalletAddRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::WALLET_ADD);
    }

    public function rules(): array
    {
        $types = array_column(Wallets::cases(), 'value');

        return [
            'name' => 'nullable|string',
            'type' => 'required|string|in:' . implode(',', $types),
            'address' => 'required|string',
        ];
    }
}
