<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class SuccessResponse extends JsonResponse
{
    public function __construct(array|object|null $data, int $status = 200, array $meta = [], array $total = [])
    {
        parent::__construct(
            array_merge(
                ['success' => true],
                ['data' => $data],
                ['meta' => $meta],
                ['total' => $total]
            ), $status
        );
    }
}
