<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\EventTypes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\TelegramService;

/**
 * @property integer $id
 * @property integer|null $user_id
 * @property EventTypes $action;
 * @property string|null $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
*/
class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    private static function castActionName(EventTypes $action): string
    {
        if ($action == EventTypes::SIGNED_UP) {
            return 'Регистрация пользователя';
        } elseif ($action == EventTypes::DEPOSIT_CHECKED) {
            return 'Проверка депозита';
        } elseif ($action == EventTypes::WITHDRAWAL_REQUESTED) {
            return 'Запрос на вывод средств';
        } else {
            return $action->value;
        }

    }

    public static function log(EventTypes $action, ?int $userId, ?string $description = null): void
    {
        $event = new self();

        // For some actions send Telegram message to the admins
        // Important actions: 1) User registration 2) User deposit 3) User withdrawal 4) User KYC verification
        $importantActions = [
            EventTypes::SIGNED_UP,
            EventTypes::DEPOSIT_CHECKED,
            EventTypes::WITHDRAWAL_REQUESTED,
        ];

        if (in_array($action, $importantActions)) {
            $message = [
                'Событие' => self::castActionName($action),
                'ID юзера' => $userId,
                'Описание' => $description,
            ];

            TelegramService::sendMessage($message);
        }

        $event->user_id = $userId;
        $event->action = $action;
        $event->description = $description;

        $event->save();
    }
}
