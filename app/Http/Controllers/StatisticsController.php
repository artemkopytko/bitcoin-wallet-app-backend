<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DepositStatuses;
use App\Http\Requests\Statistics\ReadStatisticsRequest;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController
{
    public function browse(ReadStatisticsRequest $request): JsonResponse
    {
        // For that method following data needs to be returned:
        // 1. Amount of users whose created_at date is between from_date and to_date
        // 2. Amount of users whose created_at date is between from_date and to_date and have at least one deposit
        // 3. Amount of users whose created_at date is between from_date and to_date and have at least two deposits
        // 4. Amount and sum of deposits
        // 5. Amount and sum of deposits where is_ftd is true and status is completed, pending, approved, new or processing and created_at date is between from_date and to_date
        // 6. Amount and sum of deposits where is_ftd is false and status is completed, pending, approved, new or processing and created_at date is between from_date and to_date
        // return

        $query = User::query();
        $query->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);

        $usersCount = $query->count();

        $query->whereHas('deposits', function ($query) {
            $query->whereIn('status', [
                DepositStatuses::COMPLETED,
                DepositStatuses::PENDING,
                DepositStatuses::APPROVED,
                DepositStatuses::NEW,
                DepositStatuses::PROCESSING
            ]);
        });

        $activeUsersCount = User::query()
            ->whereBetween(
                'created_at',
                [$request->input('from_date'),
                    $request->input('to_date')
                ]
            )
            ->where('is_active', '=', 1)->count();

        $usersWithVerifiedEmailsCount = User::query()
            ->whereBetween(
                'created_at',
                [$request->input('from_date'),
                    $request->input('to_date')
                ]
            )->whereNotNull('email_verified_at')->count();


        $userWith2FAEnabledCount = User::query()
            ->whereBetween(
                'created_at',
                [$request->input('from_date'),
                    $request->input('to_date')
                ]
            )->where('is_2fa_enabled', '=', 1)->count();

        $usersWithAtLeastOneDepositCount = $query->count();

        $query->whereHas('deposits', function ($query) {
            $query->whereIn('status', [
                DepositStatuses::COMPLETED,
                DepositStatuses::PENDING,
                DepositStatuses::APPROVED,
                DepositStatuses::NEW,
                DepositStatuses::PROCESSING
            ]);
        }, '>=', 2);

        $usersWithAtLeastTwoDepositsCount = $query->count();

        $deposits = Deposit::query()
            ->whereIn('status', [
                DepositStatuses::COMPLETED,
                DepositStatuses::PENDING,
                DepositStatuses::APPROVED,
                DepositStatuses::NEW,
                DepositStatuses::PROCESSING
            ])
            ->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')])
            ->get();

        $depositsCount = $deposits->count();
        $depositsSum = $deposits->sum('amount');

        $ftdDeposits = $deposits->filter(function ($deposit) {
            return $deposit->is_ftd;
        });

        $depositsCountWhereIsFtd = $ftdDeposits->count();
        $depositsSumWhereIsFtd = $ftdDeposits->sum('amount');

        $nonFtdDeposits = $deposits->filter(function ($deposit) {
            return !$deposit->is_ftd;
        });

        $depositsCountWhereIsNotFtd = $nonFtdDeposits->count();
        $depositsSumWhereIsNotFtd = $nonFtdDeposits->sum('amount');

        return response()->json(
            [
            'success' => true,
            'data' => [
            'users_count' => $usersCount,
            'active_users_count' => $activeUsersCount,
            'users_with_verified_emails_count' => $usersWithVerifiedEmailsCount,
            'users_with_2fa_enabled_count' => $userWith2FAEnabledCount,
            'users_with_at_least_one_deposit_count' => $usersWithAtLeastOneDepositCount,
            'users_with_at_least_two_deposits_count' => $usersWithAtLeastTwoDepositsCount,
            'deposits_count' => $depositsCount,
            'deposits_sum' => $depositsSum,
            'deposits_count_where_is_ftd' => $depositsCountWhereIsFtd,
            'deposits_sum_where_is_ftd' => $depositsSumWhereIsFtd,
            'deposits_count_where_is_not_ftd' => $depositsCountWhereIsNotFtd,
            'deposits_sum_where_is_not_ftd' => $depositsSumWhereIsNotFtd,
            ]
        ]);
    }

    public function userBalances(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (float) $user->balance,
                'deposits' => $user->sumOfSuccessDeposits(),
                'withdrawals' => $user->sumOfSuccessWithdrawals(),
            ]
        ]);
    }
}
