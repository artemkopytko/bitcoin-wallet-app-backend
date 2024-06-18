<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Enums\ApiError;
use Illuminate\Http\JsonResponse;

class ErrorResponse extends JsonResponse
{
    public function __construct(ApiError $message, int $status = 400)
    {
        parent::__construct(['success' => false, 'errors' => ['message' => [$message]]], $status);
    }
}
