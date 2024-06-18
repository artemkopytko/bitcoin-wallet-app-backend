<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Enums\Permissions;
use App\Http\Requests\JsonRequest;

class EventReadRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::EVENT_READ);
    }

    public function rules(): array
    {
        return [
            'sort_by' => 'string|in:id,created_at',
            'sort_order' => 'string|in:asc,desc',
            'per_page' => 'integer|min:1|max:100',
            'user_id' => 'integer|exists:users,id',
        ];
    }
}
