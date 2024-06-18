<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Http\Requests\Deposit\DepositReadRequest;
use App\Http\Requests\Wallet\WalletAddRequest;
use App\Http\Requests\Wallet\WalletDeleteRequest;
use App\Http\Requests\Wallet\WalletEditRequest;
use App\Http\Requests\Wallet\WalletReadRequest;
use App\Http\Resources\Deposit\DepositCollection;
use App\Http\Resources\Wallet\WalletCollection;
use App\Http\Resources\Wallet\WalletResource;
use App\Http\Responses\SuccessResponse;
use App\Models\Deposit;
use App\Models\User;
use App\Models\Wallet;

class WalletController extends Controller
{
   public function browse(WalletReadRequest $request): WalletCollection
   {
       $wallets = Wallet::query();

       /** @var User $user */
       $user = $request->user();

       // Non-admin users can only see active wallets
       if (!$user->hasRole(Roles::ADMIN)) {
           $wallets->where('is_active', true);

           return new WalletCollection(
               $wallets->get()
           );
       }

       if ($request->has('type')) {
           $wallets->where('type', $request->input('type'));
       }

       if ($request->has('is_active')) {
           $wallets->where('is_active', $request->input('is_active'));
       }

         if ($request->has('search')) {
              $search = $request->input('search');
              $wallets->where('name', 'like', "%$search%")
                  ->orWhere('address', 'like', "%$search%");
         }

       return new WalletCollection(
          $wallets->get()
       );
   }

    public function read(WalletReadRequest $request, Wallet $wallet): SuccessResponse
    {
         return new SuccessResponse(new WalletResource($wallet));
    }

    public function add(WalletAddRequest $request): SuccessResponse
    {
        $wallet = new Wallet();
        $this->fillAndSave($request, $wallet);

        return new SuccessResponse(new WalletResource($wallet));
    }

    public function edit(WalletEditRequest $request, Wallet $wallet): SuccessResponse
    {
        $this->fillAndSave($request, $wallet);

        return new SuccessResponse(new WalletResource($wallet));
    }

    public function delete(WalletDeleteRequest $request, Wallet $wallet): SuccessResponse
    {
        $wallet->delete();

        return new SuccessResponse(null);
    }

    public function fillAndSave(WalletAddRequest|WalletEditRequest $request, Wallet $wallet): void
    {
        $wallet->type = $request->input('type');
        $wallet->address = $request->input('address');

        if ($request->has('name')) {
            $wallet->name = $request->input('name');
        }

        if ($request->has('is_active')) {
            $wallet->is_active = $request->input('is_active');
        }

        $wallet->save();
    }

    public function deposits(DepositReadRequest $request, Wallet $wallet): DepositCollection
    {
        /** @var Deposit $items */
        $items = Deposit::where(
            'wallet_id', $wallet->id
        )->orderBy($request->input('sort_by') ?? 'id',
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

}
