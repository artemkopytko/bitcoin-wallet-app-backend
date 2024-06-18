<?php

declare(strict_types=1);

namespace App\Http\Requests\Statistics;

use App\Enums\Permissions;
use App\Http\Requests\JsonRequest;
use App\Enums\UserStatus;

class ReadStatisticsRequest extends JsonRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::STATISTICS_READ);
    }

    public function rules(): array
    {
        return [
            'from_date' => 'date_format:Y-m-d',
            'to_date' => 'date_format:Y-m-d',
        ];
    }
}
