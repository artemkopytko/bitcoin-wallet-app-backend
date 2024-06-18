<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Permissions;
use App\Enums\UserStatus;
use App\Http\Requests\JsonRequest;

class UserEditRequest extends UserEditSelfRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::USER_EDIT);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        return [
            ...$rules,
            'email' => 'email',
            'username' => 'nullable|string',
            'password' => 'nullable|string|min:8',
            'password_confirmation' => 'nullable|same:password',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'balance' => 'nullable|numeric|min:0',
        ];
    }
}
