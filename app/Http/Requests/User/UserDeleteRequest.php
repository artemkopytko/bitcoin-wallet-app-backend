<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Permissions;
use App\Enums\UserStatus;
use App\Http\Requests\JsonRequest;

class UserDeleteRequest extends JsonRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::USER_DELETE);
    }
}
