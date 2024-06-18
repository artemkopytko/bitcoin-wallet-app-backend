<?php

declare(strict_types=1);

namespace App\Http\Requests\Deposit;

use App\Enums\Permissions;
use App\Enums\Wallets;
use App\Http\Requests\JsonRequest;

class DepositEditRequest extends DepositAddRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::DEPOSIT_EDIT);
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
        ];
    }
}
