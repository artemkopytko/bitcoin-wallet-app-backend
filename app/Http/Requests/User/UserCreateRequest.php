<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Permissions;
use App\Enums\UserStatus;

class UserCreateRequest extends UserEditSelfRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::USER_CREATE);
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
            'email' => 'email|unique:users,email',
            'username' => 'nullable|string|unique:users,username',
            'password' => 'nullable|string|min:8',
            'password_repeat' => 'nullable|same:password',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'balance' => 'nullable|numeric|min:0',
            'role' => 'required|string|exists:roles,name',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ];
    }
}
