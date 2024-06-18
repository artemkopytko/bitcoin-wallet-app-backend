<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DepositStatuses;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;


/**
 * @property integer $id
 * @property string $address
 * @property Wallet $type
 * @property boolean $is_active
 * @property string|null $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Deposit[] $deposits
*/
class Wallet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'address',
        'type',
        'is_active',
        'name',
    ];

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function sumOfSuccessDeposits()
    {
        return Cache::remember('wallet_deposits_sum_' . $this->id, 60, function () {
            $res = $this->deposits()->whereIn('status', [
                    DepositStatuses::COMPLETED->value,
                    DepositStatuses::APPROVED->value,
                ]
            )->sum('amount');

            // Parse the result to a float
            return is_null($res) ? 0.0 : (float) $res;
        });
    }
}
