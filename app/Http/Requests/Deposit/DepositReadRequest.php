<?php

declare(strict_types=1);

namespace App\Http\Requests\Deposit;

use App\Enums\Permissions;
use App\Enums\Wallets;
use App\Enums\DepositStatuses;
use App\Http\Requests\JsonRequest;

class DepositReadRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::DEPOSIT_READ);
    }

    public function rules(): array
    {
        $statuses = implode(',', array_column(DepositStatuses::cases(), 'value'));

        return [
            'search' => 'nullable|string',
            'statuses' => 'bail|nullable|array',
            'statuses.*' => 'bail|nullable|string|in:' . $statuses,
            'user_ids' => 'bail|nullable|array',
            'user_ids.*' => 'bail|nullable|integer|exists:users,id',
            'staff_ids' => 'bail|nullable|array',
            'staff_ids.*' => 'bail|nullable|integer|exists:users,id',
            'wallet_ids' => 'bail|nullable|array',
            'wallet_ids.*' => 'bail|nullable|integer|exists:wallets,id',
            'empty_wallet_id' => 'bail|nullable|boolean',
            'empty_staff_id' => 'bail|nullable|boolean',
            'empty_user_id' => 'bail|nullable|boolean',
            'page' => 'bail|nullable|integer|min:1',
            'per_page' => 'bail|nullable|integer|min:1|max:100',
            'sort_by' => 'bail|nullable|string|in:id,created_at,updated_at,amount,status,user_id,staff_id,wallet_id',
            'sort_order' => 'bail|nullable|string|in:asc,desc',
        ];
    }
}
