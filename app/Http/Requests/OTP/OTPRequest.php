<?php

declare(strict_types=1);

namespace App\Http\Requests\OTP;

use App\Http\Requests\JsonRequest;

class OTPRequest extends JsonRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'one_time_password' => 'required|string|min:6|max:6'
        ];
    }
}
