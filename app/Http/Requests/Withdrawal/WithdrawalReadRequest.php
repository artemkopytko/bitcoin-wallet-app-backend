<?php

declare(strict_types=1);

namespace App\Http\Requests\Withdrawal;

use App\Enums\Permissions;
use App\Enums\Wallets;
use App\Enums\WithdrawalMethods;
use App\Enums\WithdrawalStatuses;
use App\Http\Requests\JsonRequest;

class WithdrawalReadRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::WITHDRAWAL_READ);
    }

    public function rules(): array
    {
        $statuses = implode(',', array_column(WithdrawalStatuses::cases(), 'value'));
        $methods = implode(',', array_column(WithdrawalMethods::cases(), 'value'));

        return [
            'search' => 'nullable|string',
            'statuses' => 'bail|nullable|array',
            'statuses.*' => 'bail|nullable|string|in:' . $statuses,
            'methods' => 'bail|nullable|array',
            'methods.*' => 'bail|nullable|string|in:' . $methods,
            'user_ids' => 'bail|nullable|array',
            'user_ids.*' => 'bail|nullable|integer|exists:users,id',
            'empty_user_id' => 'bail|nullable|boolean',
            'page' => 'bail|nullable|integer|min:1',
            'per_page' => 'bail|nullable|integer|min:1|max:100',
            'sort_by' => 'bail|nullable|string|in:id,created_at,updated_at,amount,method,status',
            'sort_order' => 'bail|nullable|string|in:asc,desc',
        ];
    }
}
