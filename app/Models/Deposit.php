<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DepositStatuses;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property integer|null $user_id
 * @property integer|null $wallet_id
 * @property integer|null $staff_id
 * @property boolean $is_calculated
 * @property numeric $amount
 * @property boolean|null $is_ftd
 * @property DepositStatuses $status
 * @property string|null $note
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $user
 * @property-read Wallet|null $wallet
 * @property-read User|null $staff
*/
class Deposit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'staff_id',
        'amount',
        'status',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
