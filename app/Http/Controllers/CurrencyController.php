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
use App\Models\Currency;
use App\Models\Event;
use App\Models\User;
use App\Models\Deposit;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{
    public function getBitcoinCurrency(Request $request): JsonResponse
    {
        $bitcoin = Currency::where('code', 'BTC')->first();
        $eur = Currency::where('code', 'EUR')->first();

        return new SuccessResponse([
            'usd' => (float) $bitcoin->price,
            'eur' => (float) $bitcoin->price / $eur->price,
        ]);
    }
}
