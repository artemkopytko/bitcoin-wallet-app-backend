<?php

namespace App\Listeners;

use App\Enums\EventTypes;
use App\Enums\WithdrawalStatuses;
use App\Events\WithdrawalStatusChanged;
use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OnWithdrawalStatusChanged implements ShouldQueue
{
    use InteractsWithQueue;
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
    public function handle(WithdrawalStatusChanged $event): void
    {
        try {
            $withdrawal = $event->withdrawal;

            /* @var User $user */
            $user = $withdrawal->user;

            // Log in clock
            clock()->info('In handler');

            if (!$withdrawal->is_calculated &&
                ($withdrawal->status == WithdrawalStatuses::COMPLETED->value ||
                    $withdrawal->status == WithdrawalStatuses::APPROVED->value)) {

                Event::log(EventTypes::WITHDRAWAL_PRECOMPLETED, $user->id, 'Учет вывода на сумму ' . $withdrawal->amount . '$ с баланса. Текущий баланс ' .  $user->balance . '$' );

                // Update the balance of the wallet
                $withdrawal->is_calculated = true;
                $user->balance -= (float) $withdrawal->amount;
                $withdrawal->save();
                $user->save();

                Event::log(EventTypes::WITHDRAWAL_COMPLETED, $user->id, 'Баланс уменьшен на ' . $withdrawal->amount . '$ и составляет ' . $user->balance . '$' );

                // Send an email to the user
                $user->sendWithdrawalCompletedEmail($withdrawal);
            }
        } catch (\Exception $e) {
            // Log in clock
            clock()->error('Failed to send withdrawal completed email', [
                'user_id' => $user->id ?? null,
                'withdrawal_id' => $withdrawal->id ?? null,
                'error' => $e->getMessage(),
            ]);

            // Optionally rethrow the exception if you want the job to be retried
            throw $e;
        }
    }
}
