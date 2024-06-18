<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class JsonRequest extends FormRequest
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
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()
            ->json(
                [
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422)
        );
    }
}
