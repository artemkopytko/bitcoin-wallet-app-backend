<?php

namespace App\Http\Controllers;

use App\Enums\EventTypes;
use App\Enums\Roles;
use App\Enums\DepositStatuses;
use App\Events\DepositStatusChanged;
use App\Http\Requests\Deposit\DepositAddRequest;
use App\Http\Requests\Deposit\DepositCheckRequest;
use App\Http\Requests\Deposit\DepositDeleteRequest;
use App\Http\Requests\Deposit\DepositEditRequest;
use App\Http\Requests\Deposit\DepositReadRequest;
use App\Http\Resources\Deposit\DepositCollection;
use App\Http\Resources\Deposit\DepositResource;
use App\Http\Responses\SuccessResponse;
use App\Models\Event;
use App\Models\User;
use App\Models\Deposit;
use App\Models\Wallet;
use Illuminate\Support\Facades\Cache;

class DepositController extends Controller
{
    public function browse(DepositReadRequest $request): DepositCollection
    {
        /** @var Deposit $items */
        $items = Deposit::query()
            ->with([
                'user',
                'staff:id,first_name,last_name,email',
                'wallet:id,name,address,type',
            ])
            ->orderBy($request->input('sort_by') ?? 'id',
                $request->input('sort_order') ?? 'desc');

        /** @var User $user */
        $user = $request->user();

        // User can only see their own withdrawals
        if ($user->hasRole(Roles::USER)) {
            $items->where('user_id', $user->id);
        }

        // For non-uses roles allow filter by user_id
        if (!$user->hasRole(Roles::USER)) {
            if ($request->has('user_ids')) {
                $items->whereIn('user_id', $request->input('user_ids'));
            }

            if ($request->has('staff_ids')) {
                $items->whereIn('staff_id', $request->input('staff_ids'));
            }

            if ($request->has('wallet_ids')) {
                $items->whereIn('wallet_id', $request->input('wallet_ids'));
            }

            if ($request->has('empty_user_id')) {
                $items->whereNull('user_id');
            }

            if ($request->has('empty_staff_id')) {
                $items->whereNull('staff_id');
            }

            if ($request->has('empty_wallet_id')) {
                $items->whereNull('wallet_id');
            }

            if ($request->has('statuses')) {
                $items->whereIn('status', $request->input('statuses'));
            }

            if ($request->has('search')) {
                $search = $request->input('search');
                $items->where('note', 'like', "%$search%");
            }
        }

        return new DepositCollection(
            $items->paginate(
                $request->input('per_page', 25)
            )
        );
    }

    public function read(DepositReadRequest $request, Deposit $item): SuccessResponse
    {
        return new SuccessResponse(new DepositResource($item));
    }

    public function add(DepositAddRequest $request): SuccessResponse
    {
        /** @var User $user */
        $user = $request->user();

        $item = new Deposit();
        $this->fillAndSave($request, $item);

        if ($item->user_id) {
            Event::log(EventTypes::DEPOSIT_ADDED, $item->user_id, 'Deposited ' . $item->amount . '. Added by manager id ' . $item->staff_id);
        }

        return new SuccessResponse(new DepositResource($item));
    }

    public function fillAndSave(DepositAddRequest|DepositEditRequest $request, Deposit $item): void
    {
        $item->user_id = $request->input('user_id') ?? null;
        $item->wallet_id = $request->input('wallet_id') ?? null;
        $item->staff_id = $request->user()->id ?? null;
        $item->amount = $request->input('amount');
        $item->note = $request->input('note');

        // If user has no deposits - set deposit is_ftd to true
        if ($item->user_id) {
            $userDepositsCount = Deposit::where('user_id', $item->user_id)->count();
            $item->is_ftd = $userDepositsCount === 0;
        }

        if ($request->has('status')) {
            clock()->info('Deposit status changed', [
                'deposit_id' => $item->id,
                'status' => $request->input('status'),
            ]);

            $item->status = $request->input('status');

            DepositStatusChanged::dispatch($item);
        } else {
            $item->status = DepositStatuses::NEW;
        }

        $item->save();
    }

    public function edit(DepositEditRequest $request, Deposit $item): SuccessResponse
    {
        $this->fillAndSave($request, $item);

//        Event log
        if ($item->user_id) {
            Event::log(EventTypes::DEPOSIT_EDITED, $item->user_id, 'Deposit edited. Amount: ' . $item->amount . '. Note: ' . $item->note);
        }

        return new SuccessResponse(new DepositResource($item));
    }

    public function delete(DepositDeleteRequest $request, Deposit $item): SuccessResponse
    {
        $item->delete();

        return new SuccessResponse(null);
    }

    public function checkDeposit(DepositCheckRequest $request): SuccessResponse
    {
        // That request takes wallet_id and amount. And sends event to log, that user asked to checked the deposit
        // 1. Check if cache is not set for that request

        /** @var User $user */
        $user = $request->user();

        $amount = $request->input('amount');
        $walletId = $request->input('wallet_id');

        $alreadyRequested = Cache::get('deposit_check_' . $user->id . '_' . $walletId . '_' . $amount);

        if ($alreadyRequested) {
            return new SuccessResponse(null);
        }

        $wallet = Wallet::where('id', $walletId)->first();

        $message = 'Запрос на проверку депозита. Сумма: ' . $amount;

        if ($wallet) {
            $message .= '. Кошелек: [' . $wallet->id . '] ' . $wallet->name;
        }

        Event::log(EventTypes::DEPOSIT_CHECKED, $user->id, $message);

        // Create cache for that request to prevent spam
        Cache::put('deposit_check_' . $user->id . '_' . $walletId . '_' . $amount, true, 60);

        return new SuccessResponse(null);
    }
}
