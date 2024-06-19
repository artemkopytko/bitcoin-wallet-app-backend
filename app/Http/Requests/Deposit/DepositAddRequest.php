<?php

declare(strict_types=1);

namespace App\Http\Requests\Deposit;

use App\Enums\Permissions;
use App\Enums\DepositStatuses;
use App\Http\Requests\JsonRequest;

class DepositAddRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::DEPOSIT_ADD);
    }

    public function rules(): array
    {
        $statuses = implode(',', array_column(DepositStatuses::cases(), 'value'));

        return [
            'user_id' => 'nullable|integer|exists:users,id',
            'wallet_id' => 'nullable|integer|exists:wallets,id',
            'amount' => 'required|numeric|min:0.00001',
            'status' => 'nullable|integer|in:' . $statuses,
            'note' => 'nullable|string',
        ];
    }
}
