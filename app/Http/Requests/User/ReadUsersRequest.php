<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Permissions;
use App\Http\Requests\JsonRequest;
use App\Enums\UserStatus;

class ReadUsersRequest extends JsonRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::USER_READ);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sort_by' => ['bail', 'nullable', 'string', 'in:id,created_at,updated_at,is_2fa_enabled,email_verified_at,is_kyc_pending,is_kyc_verified,is_active,first_name,last_name,email,phone,status,balance,deposit_sum'],
            'sort_direction' => ['bail', 'nullable', 'string', 'in:desc,asc'],
            'country_codes' => ['bail', 'nullable', 'array'],
            'country_codes.*' => ['bail', 'nullable', 'string'],
            'search' => ['bail', 'nullable', 'string'],
            'page' => ['bail', 'nullable', 'integer', 'min:1'],
            'per_page' => ['bail', 'nullable', 'integer', 'min:1', 'max:100'],
            'is_2fa_enabled' => ['bail', 'nullable', 'boolean'],
            'is_email_verified' => ['bail', 'nullable', 'boolean'],
            'has_deposit' => ['bail', 'nullable', 'boolean'],
            'is_active' => ['bail', 'nullable', 'boolean'],
            'role' => ['bail', 'nullable', 'string', 'exists:roles,name'],
        ];
    }
}
