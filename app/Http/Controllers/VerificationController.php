<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ApiError;
use App\Enums\ApiMessage;
use App\Enums\EventTypes;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailVerificationRequest;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    // Ignore auth middleware
    public function __construct()
    {
//        $this->middleware('auth');
//        $this->middleware('signed')->only('verify');
        $this->middleware(['throttle:6,1',  'jwt.cookie'])->only('verify', 'resend');
    }

    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        $expires = $request->input('expires');

        if ($expires < now()->timestamp) {
            // Verification link has expired
            return new ErrorResponse(ApiError::EMAIL_CONFIRMATION_LINK_EXPIRED, 422);
        }

//        TODO: Check if signature is valid
//        TODO: Check if user id = request (auth) user id

        $user = User::findOrFail($request->input('id'));
        $hash = $request->input('hash');

        $expectedHash = sha1($user->email); // Example hash generation based on user's email
        if ($hash !== $expectedHash) {
            // Hash mismatch
            return new ErrorResponse(ApiError::EMAIL_CONFIRMATION_HASH_MISMATCH, 422);
        }

        if ($user->email_verified_at) {
            // Email already verified
            return new ErrorResponse(ApiError::EMAIL_ALREADY_VERIFIED, 422);
        }

        $user->email_verified_at = now();
        $user->save();

        Event::log(EventTypes::EMAIL_VERIFIED, $user->id, 'User email verified');

        return new SuccessResponse((Object)['message' => ApiMessage::EMAIL_VERIFIED_SUCCESS], 200);
    }


    public function resend(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->email_verified_at) {
            // Email already verified
            return new ErrorResponse(ApiError::EMAIL_ALREADY_VERIFIED, 422);
        }

        $request->user()->sendEmailVerificationNotification();

        return new SuccessResponse((Object)['message' => ApiMessage::EMAIL_VERIFICATION_LINK_SENT], 200);
    }
}
