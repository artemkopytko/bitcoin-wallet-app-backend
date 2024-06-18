<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\Permissions;
use App\Http\Requests\JsonRequest;
use App\Enums\UserStatus;

class ReadUserRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::USER_READ);
    }
}
