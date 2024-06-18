<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Permissions;
use App\Http\Requests\JsonRequest;

class UserEditSelfRequest extends JsonRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::USER_EDIT_SELF);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
        ];
    }
}
