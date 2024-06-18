<?php

declare(strict_types=1);

namespace App\Http\Requests\Withdrawal;

use App\Enums\Permissions;
use App\Enums\Wallets;
use App\Enums\WithdrawalMethods;
use App\Enums\WithdrawalStatuses;
use App\Http\Requests\JsonRequest;

class WithdrawalAddRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::WITHDRAWAL_ADD);
    }

    public function rules(): array
    {
        $statuses = implode(',', array_column(WithdrawalStatuses::cases(), 'value'));
        $methods = implode(',', array_column(WithdrawalMethods::cases(), 'value'));

        return [
            'user_id' => 'nullable|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'requisites' => 'required|string',
            'status' => 'nullable|integer|in:' . $statuses,
            'method' => 'nullable|integer|in:' . $methods,
            'admin_notes' => 'nullable|string',
        ];
    }
}
