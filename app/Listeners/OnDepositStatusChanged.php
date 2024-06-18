<?php

namespace App\Listeners;

use App\Enums\DepositStatuses;
use App\Enums\EventTypes;
use App\Events\DepositStatusChanged;
use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OnDepositStatusChanged
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DepositStatusChanged $event): void
    {
        $deposit = $event->deposit;

        /* @var User $user */
        $user = $deposit->user;

        if (!$deposit->is_calculated &&
            ($deposit->status == DepositStatuses::APPROVED->value ||
            $deposit->status == DepositStatuses::COMPLETED->value)) {

            Event::log(EventTypes::DEPOSIT_PRECOMPLETED, $user->id, 'Зачисление депозита ' . $deposit->amount . '$ на баланс. Текущий баланс ' .  $user->balance . '$' );

            $deposit->is_calculated = true;
            $user->balance += (float) $deposit->amount;
            $user->save();
            $deposit->save();

            Event::log(EventTypes::DEPOSIT_COMPLETED, $user->id, 'Баланс увеличен на ' . $deposit->amount . '$ и составляет ' . $user->balance . '$' );

            $user->sendDepositCompletedEmail($deposit);
        }
    }
}
