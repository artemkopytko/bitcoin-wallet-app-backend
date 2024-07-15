<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ApiError;
use App\Http\Responses\ErrorResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Sentry\Laravel\Integration;
use Throwable;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Integration::captureUnhandledException($e);
        });
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return ErrorResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception): ErrorResponse
    {
        return new ErrorResponse(ApiError::UNAUTHORIZED, 401);
    }
}
