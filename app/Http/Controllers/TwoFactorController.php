<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EventTypes;
use App\Http\Requests\OTP\OTPRequest;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FAQRCode\Google2FA;
use App\Enums\ApiError;
use App\Enums\ApiMessage;

class TwoFactorController extends Controller
{
    public function enable(Request $request): JsonResponse
    {
        // Generate secret key for 2FA
        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();

        // Save the secret key to the user's record in the database
        /** @var User $user */
        $user = $request->user();

        if ($user->is_2fa_enabled) {
            return new ErrorResponse(ApiError::OTP_ALREADY_ENABLED, 422);
        }

        $user->google2fa_secret = Crypt::encryptString($secretKey);
        $user->save();

        Event::log(EventTypes::REQUESTED_2FA_SETUP, $user->id, 'User requested 2FA setup');

        // Return the secret key and QR code URL for the user to scan
        return new SuccessResponse((Object)[
            'secret_key' => $secretKey,
            'qr_code_url' => $google2fa->getQRCodeInline(
                config('app.name'),
                $user->email,
                $secretKey
            )
        ], 200);
    }

    public function verify(OTPRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->google2fa_secret) {
            return new ErrorResponse(ApiError::OTP_NOT_ENABLED, 422);
        }

        if ($user->is_2fa_enabled) {
            return new ErrorResponse(ApiError::OTP_ALREADY_ENABLED, 422);
        }

        $otp = $request->input('one_time_password');
        $google2fa = new Google2FA();

        $verified = $google2fa->verifyKey(
            Crypt::decryptString($user->google2fa_secret),
            $otp
        );

        if (!$verified) {
            Event::log(EventTypes::REQUESTED_2FA_SETUP_FAILURE, $user->id, 'User failed 2FA setup');
            return new ErrorResponse(ApiError::OTP_INVALID_CODE, 422);
        }

        $user->is_2fa_enabled = true;
        $user->save();

        Event::log(EventTypes::REQUESTED_2FA_SETUP_SUCCESS, $user->id, 'User set up 2FA successfully');

        return new SuccessResponse((Object)[
            'message' => ApiMessage::OTP_ENABLED_SUCCESS,
        ], 200);
    }

    public function disable(OTPRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $otp = $request->input('one_time_password');
        $google2fa = new Google2FA();

        if (!$user->google2fa_secret) {
            return new ErrorResponse(ApiError::OTP_NOT_ENABLED, 422);
        }

        if (!$user->is_2fa_enabled) {
            return new ErrorResponse(ApiError::OTP_ALREADY_DISABLED, 422);
        }

        if (!$google2fa->verifyKey(
            Crypt::decryptString($user->google2fa_secret),
            $otp
        )) {
            return new ErrorResponse(ApiError::OTP_INVALID_CODE, 422);
        }

        $user->is_2fa_enabled = false;
        $user->google2fa_secret = null;
        $user->save();

        Event::log(EventTypes::REQUESTED_2FA_DISABLE, $user->id, 'User disabled 2FA');

        return new SuccessResponse((Object)[
            'message' => ApiMessage::OTP_DISABLED_SUCCESS,
        ], 200);
    }
}
