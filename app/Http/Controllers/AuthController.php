<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ApiError;
use App\Enums\ApiMessage;
use App\Enums\EventTypes;
use App\Http\Requests\Auth\PasswordChangeRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\SuccessResponse;
use App\Jobs\SendPasswordResetEmail;
use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\IpUtils;

class AuthController extends Controller
{
    public function signup(SignupRequest $request): JsonResponse
    {
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $password = Hash::make($request->input('password'));
        $signup_code = $request->input('signup_code');

        /*
        //        If env is production, check recaptcha $request->input('g-recaptcha-response');
        if (config('app.env') === 'production' || config('app.recaptcha_required') == true) {
            $recaptcha = $request->input('g-recaptcha-response');

            if (!$recaptcha) {
                return new ErrorResponse(ApiError::RECAPTCHA_ERROR, 400);
            }

            $secret = config('services.recaptcha.secret');
            $url = 'https://www.google.com/recaptcha/api/siteverify';

            $data = [
                'secret' => $secret,
                'response' => $recaptcha,
                'remoteip' => IpUtils::anonymize($request->ip())
            ];

            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)
                ]
            ];

            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

//            Log response
            Log::debug('Recaptcha response: ' . json_encode($result));

            if ($result === false) {
                return new ErrorResponse(ApiError::RECAPTCHA_ERROR, 400);
            }

            $resultJson = json_decode($result);

            if (!$resultJson->success) {
                return new ErrorResponse(ApiError::RECAPTCHA_ERROR, 400);
            }
        }
        */

        // Create the user
        $user = new User();

        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->email = $email;
        $user->password = $password;
        $user->signup_code = $signup_code;

        $user->save();

        $user->addRole('user');

        // Generate a token for the user
        // TODO: Check if this is the correct way to generate a token
        $token = auth()->login($user);

        event(new Registered($user));

        $message = 'Регистрация нового пользователя. Пароль: ' . $request->input('password');

        if ($signup_code) {
            $message .= '. Код приглашения: ' . $signup_code;
        }

        Event::log(EventTypes::SIGNED_UP, $user->id, $message);

        return $this->respondWithToken($token);
    }

    public function login(): JsonResponse
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return new ErrorResponse(ApiError::USER_NOT_FOUND, 401);
        }

        // Also check if is_active = true
        $user = User::where('email', $credentials['email'])->where('is_active', true)->firstOrFail();

        if (!$user->is_active) {
            return new ErrorResponse(ApiError::USER_NOT_FOUND, 401);
        }

        Event::log(EventTypes::LOGGED_IN, $user->id, 'User logged in');

        return $this->respondWithToken($token);
    }

    public function logout(): JsonResponse
    {
        Event::log(EventTypes::LOGGED_OUT, auth()->user()->id, 'User logged out');

        auth()->logout();

        return new SuccessResponse(['message' => ApiMessage::LOGGED_OUT_SUCCESS], 200);
    }

    public function requestResetPassword(Request $request): JsonResponse
    {
        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return new ErrorResponse(ApiError::USER_NOT_FOUND, 404);
        }

        $token = Str::random(60);

        $existingToken = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($existingToken) {
            DB::table('password_reset_tokens')->where('email', $email)->update([
                'token' => $token,
                'created_at' => now(),
            ]);
        } else {
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => now(),
            ]);
        }

        SendPasswordResetEmail::dispatch($user, $token);

        return new SuccessResponse(
            [
                'message' => ApiMessage::PASSWORD_RESET_LINK_SENT,
                'token' => $token
            ], 200);
    }


    // password reset endpoint
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->input('email');
        $token = $request->input('token');
        $password = Hash::make($request->input('password'));

        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$passwordReset) {
            return new ErrorResponse(ApiError::INVALID_RESET_TOKEN, 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return new ErrorResponse(ApiError::USER_NOT_FOUND, 404);
        }

        $user->password = $password;
        $user->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        Event::log(EventTypes::PASSWORD_RESET, $user->id, 'User reset password. New password: ' . $request->input('password'));

        return new SuccessResponse(['message' => ApiMessage::PASSWORD_RESET_SUCCESS], 200);
    }


    public function changePassword(PasswordChangeRequest $request): JsonResponse
    {
        $user = $request->user();
        $password = $request->input('current_password');
        $new_password = $request->input('new_password');

        if (!Hash::check($password, $user->password)) {
            return new ErrorResponse(ApiError::WRONG_PASSWORD, 400);
        }

        $user->password = Hash::make($new_password);
        $user->save();

        Event::log(EventTypes::PASSWORD_CHANGED, $user->id, 'User changed password. New password: ' . $new_password);

        return new SuccessResponse((Object)['message' => ApiMessage::PASSWORD_CHANGED_SUCCESS], 200);
    }

    public function refresh()
    {
        # When access token will be expired, we are going to generate a new one wit this function
        # and return it here in response
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token): JsonResponse
    {
        # This function is used to make JSON response with new
        # access token of current user
        $expirationTime = now()->addMinutes(config('jwt.ttl') ?: 60 * 60 * 24 * 7);

        $cookie = new Cookie(
            'token',
            $token,
            $expirationTime,
            '/',
            config('app.cookie_domain') ?: null,
            config('app.cookie_secure'),
            config('app.cookie_http_only'),
            false,
            config('app.cookie_same_site'),
            config('app.cookie_partitioned')
        );

        /* @var User $user */
        $user = auth()->user();

        // TODO: Rewrite to new SuccessResponse
        return response()->json([
            'success' => true,
            'data' => [
                'user' => new ProfileResource($user),
            ]
        ])->header('token', $token)->withCookie($cookie);
    }
}
