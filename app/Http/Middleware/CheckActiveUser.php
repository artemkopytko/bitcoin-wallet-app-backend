<?php

namespace App\Http\Middleware;

use App\Enums\ApiError;
use App\Http\Responses\ErrorResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->is_active) {
            // Logout user
            auth()->logout();

            return new ErrorResponse(ApiError::USER_NOT_ACTIVE, 403);
        }

        return $next($request);
    }
}
