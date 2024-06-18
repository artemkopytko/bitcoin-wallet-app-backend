<?php

declare(strict_types=1);

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetHoldingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CreditPositionController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SpotOrderController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Responses\SuccessResponse;
use App\Models\User;
use App\Notifications\TestBroadcastNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(
    ['prefix' => 'auth'],
    function (): void {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('signup', [AuthController::class, 'signup'])->name('signup');
        Route::group(
            ['middleware' => 'auth:api'],
            function (): void {
                Route::post('logout', [AuthController::class, 'logout']);
                Route::post('refresh', [AuthController::class, 'refresh']);
                Route::post('password', [AuthController::class, 'changePassword']);
                Route::post('password/request-reset', [AuthController::class, 'requestResetPassword']);
                Route::post('password/reset', [AuthController::class, 'resetPassword']);
            }
        );
        Route::group(
            ['prefix' => 'email'],
            function (): void {
                Route::post('/resend', [VerificationController::class, 'resend'])->name('verification.verify');
            }
        );
        Route::group(
            ['prefix' => '2fa', 'middleware' => 'auth:api'],
            function (): void {
                Route::post('/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
                Route::post('/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
                Route::post('/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
            }
        );
    }
);

Route::group(
    ['prefix' => 'profile', 'middleware' => 'auth:api'],
    function (): void {
        Route::get('/', [UserController::class, 'profile']);
        Route::post('/', [UserController::class, 'editSelf']);
        Route::post('avatar', [UserController::class, 'changeAvatar']);
    }
);

Route::group(
    ['prefix' => 'events', 'middleware' => 'auth:api'],
    function (): void {
        Route::get('/', [EventController::class, 'browse']);
        Route::get('/{event}', [EventController::class, 'read']);
    }
);

Route::group(
    ['prefix' => 'wallets', 'middleware' => 'auth:api'],
    function (): void {
        Route::get('/', [WalletController::class, 'browse']);
        Route::post('/', [WalletController::class, 'add']);
        Route::get('/{wallet}', [WalletController::class, 'read']);
        Route::post('/{wallet}', [WalletController::class, 'edit']);
        Route::delete('/{wallet}', [WalletController::class, 'delete']);
        Route::get('/{wallet}/deposits', [WalletController::class, 'deposits']);
    }
);

Route::group(
    ['prefix' => 'withdrawals', 'middleware' => 'auth:api'],
    function (): void {
        Route::get('/', [WithdrawalController::class, 'browse']);
        Route::post('/', [WithdrawalController::class, 'add']);
        Route::get('/{withdrawal}', [WithdrawalController::class, 'read']);
        Route::post('/{withdrawal}', [WithdrawalController::class, 'edit']);
        Route::delete('/{withdrawal}', [WithdrawalController::class, 'delete']);
    }
);

Route::group(
    ['prefix' => 'deposits', 'middleware' => 'auth:api'],
    function (): void {
        Route::get('/', [DepositController::class, 'browse']);
        Route::post('/', [DepositController::class, 'add']);
        Route::post('/check-deposit', [DepositController::class, 'checkDeposit']);
        Route::get('/{deposit}', [DepositController::class, 'read']);
        Route::post('/{deposit}', [DepositController::class, 'edit']);
        Route::delete('/{deposit}', [DepositController::class, 'delete']);
    }
);

Route::group(
    ['prefix' => 'users', 'middleware' => 'auth:api'],
    function (): void {
        Route::get('/', [UserController::class, 'browse']);
        Route::post('/', [UserController::class, 'create']);
        Route::get('/{user}', [UserController::class, 'read']);
        Route::post('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'delete']);
        Route::get('/{user}/deposits', [UserController::class, 'deposits']);
        Route::get('/{user}/withdrawals', [UserController::class, 'withdrawals']);
        Route::get('/{user}/events', [UserController::class, 'events']);
    }
);

Route::group(
    ['prefix' => 'statistics', 'middleware' => 'auth:api'],
    function (): void {
        Route::get('/', [StatisticsController::class, 'browse']);
        Route::get('/balances', [StatisticsController::class, 'userBalances']);
    }
);

