<?php

declare(strict_types=1);

namespace App\Http\Requests\Wallet;

use App\Enums\Permissions;
use App\Enums\Wallets;
use App\Http\Requests\JsonRequest;

class WalletDeleteRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::WALLET_DELETE);
    }
}
