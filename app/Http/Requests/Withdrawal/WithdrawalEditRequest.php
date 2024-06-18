<?php

declare(strict_types=1);

namespace App\Http\Requests\Withdrawal;

use App\Enums\Permissions;
use App\Enums\Wallets;
use App\Http\Requests\JsonRequest;

class WithdrawalEditRequest extends WithdrawalAddRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::WITHDRAWAL_EDIT);
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
        ];
    }
}
