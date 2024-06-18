<?php

namespace App\Http\Controllers;

use App\Enums\DepositStatuses;
use App\Enums\EventTypes;
use App\Enums\Roles;
use App\Enums\WithdrawalStatuses;
use App\Events\WithdrawalStatusChanged;
use App\Http\Requests\Withdrawal\WithdrawalAddRequest;
use App\Http\Requests\Withdrawal\WithdrawalDeleteRequest;
use App\Http\Requests\Withdrawal\WithdrawalEditRequest;
use App\Http\Requests\Withdrawal\WithdrawalReadRequest;
use App\Http\Resources\Withdrawal\WithdrawalCollection;
use App\Http\Resources\Withdrawal\WithdrawalResource;
use App\Http\Responses\SuccessResponse;
use App\Models\Event;
use App\Models\User;
use App\Models\Withdrawal;
use Date;

class WithdrawalController extends Controller
{
    public function browse(WithdrawalReadRequest $request): WithdrawalCollection
    {
        clock()->info('WithdrawalController.browse start' . Date::now()->format('Y-m-d H:i:s.u') . microtime(true));
        /** @var Withdrawal $items */
        $items = Withdrawal::query()
            ->with([
                'user:id,first_name,last_name,email',
            ])
            ->orderBy($request->input('sort_by') ?? 'id',
                $request->input('sort_order') ?? 'desc');

        /** @var User $user */
        $user = $request->user();

        // User can only see their own withdrawals
        if ($user->hasRole(Roles::USER)) {
            $items->where('user_id', $user->id);
        }

        // For non-user roles allow filter by user_id
        if (!$user->hasRole(Roles::USER)) {
            if ($request->has('user_ids')) {
                $items->whereIn('user_id', $request->input('user_ids'));
            }

           if ($request->has('methods')) {
               $items->whereIn('method', $request->input('methods'));
           }

           if ($request->has('empty_user_id')) {
               $items->whereNull('user_id');
           }

           if ($request->has('statuses')) {
               $items->whereIn('status', $request->input('statuses'));
           }

           if ($request->has('search')) {
               $search = $request->input('search');
               $items->where('requisites', 'like', "%$search%")
                   ->orWhere('admin_notes', 'like', "%$search%");
           }
        }

        clock()->info('WithdrawalController.browse end' . Date::now()->format('Y-m-d H:i:s.u') . microtime(true));

        return new WithdrawalCollection(
            $items->paginate(
                $request->input('per_page', 25)
            )
        );
    }

    public function read(WithdrawalReadRequest $request, Withdrawal $item): SuccessResponse
    {
        return new SuccessResponse(new WithdrawalResource($item));
    }

    public function add(WithdrawalAddRequest $request): SuccessResponse
    {
        /** @var User $user */
        $user = $request->user();

        // User can only add their own withdrawals
        if ($user->hasRole(Roles::USER)) {
            $request->merge(['user_id' => $user->id]);
        }

        $item = new Withdrawal();
        $this->fillAndSave($request, $item);


        if ($user->hasRole(Roles::USER)) {
            Event::log(EventTypes::WITHDRAWAL_REQUESTED, $user->id, 'Запрос на вывод средств. Сумма: ' . $item->amount . '. Реквизиты: ' . $item->requisites);
        }

        return new SuccessResponse(new WithdrawalResource($item));
    }

    public function edit(WithdrawalEditRequest $request, Withdrawal $item): SuccessResponse
    {
        $this->fillAndSave($request, $item);

        return new SuccessResponse(new WithdrawalResource($item));
    }

    public function delete(WithdrawalDeleteRequest $request, Withdrawal $item): SuccessResponse
    {
        $item->delete();

        return new SuccessResponse(null);
    }

    public function fillAndSave(WithdrawalAddRequest|WithdrawalEditRequest $request, Withdrawal $item): void
    {
        $item->user_id = $request->input('user_id');
        $item->amount = $request->input('amount');
        $item->requisites = $request->input('requisites');
        $item->currency = $request->input('currency');

        if ($request->has('status')) {
            $item->status = $request->input('status');

            WithdrawalStatusChanged::dispatch($item);
            Event::log(EventTypes::WITHDRAWAL_COMPLETED, $item->user_id, 'Withdrawal completed. Amount: ' . $item->amount . '. Requisites: ' . $item->requisites);

        } else {
            $item->status = WithdrawalStatuses::NEW;
        }

        if ($request->has('method')) {
            $item->method = $request->input('method');
        }

        if ($request->has('admin_notes')) {
            $item->admin_notes = $request->input('admin_notes');
        }

        $item->save();
    }
}
