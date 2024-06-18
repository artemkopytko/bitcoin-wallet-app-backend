<?php

declare(strict_types=1);

namespace App\Http\Requests\Deposit;

use App\Enums\Permissions;
use App\Enums\DepositStatuses;
use App\Http\Requests\JsonRequest;

class DepositCheckRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::DEPOSIT_READ);
    }

    public function rules(): array
    {
        return [
            'wallet_id' => 'nullable|integer|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01',
        ];
    }
}
