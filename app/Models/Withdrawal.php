<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WithdrawalMethods;
use App\Enums\WithdrawalStatuses;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property integer $id
 * @property integer $user_id
 * @property numeric $amount
 * @property string $currency
 * @property string $requisites
 * @property boolean $is_calculated
 * @property WithdrawalStatuses $status
 * @property WithdrawalMethods|null $method
 * @property string|null $admin_notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $user
*/
class Withdrawal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount',
        'requisites',
        'status',
        'method',
        'admin_notes',
        'currency'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
