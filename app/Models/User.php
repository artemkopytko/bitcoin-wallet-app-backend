<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\DepositStatuses;
use App\Enums\EventTypes;
use App\Enums\StorageTypes;
use App\Enums\WithdrawalStatuses;
use App\Notifications\DepositCompleted;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\WithdrawalCompleted;
use Carbon\Carbon;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $signup_code
 * @property string $email
 * @property string $password
 * @property string $remember_token
 * @property float $balance // Balance in BTC
 * @property string|null $notes
 * @property string|null $avatar
 * @property string|null $google2fa_secret
 * @property bool $is_2fa_enabled
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $deleted_at
 * @property-read Event[]|null $events
 * @property-read Withdrawal[]|null $withdrawals
 * @property-read Deposit[]|null $deposits
 *
 * Scopes
 * @method static Builder|static whereStatusIn(mixed $status)
 * @method static Builder|static whereCountryCodeIn(mixed $status)
*/

class User extends Authenticatable implements LaratrustUser, JWTSubject, MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use HasRolesAndPermissions;
    use SoftDeletes;
    use CanResetPassword;

//    Boot. On user creation, create an asset holding for each asset
    public static function boot()
    {
        parent::boot();

        // on balance updated
        static::updating(function ($user) {

            if ($user->isDirty('balance')) {
                $oldBalance = $user->getOriginal('balance');
                $newBalance = $user->balance;

                Log::info('User balance updated', [
                    'user_id' => $user->id,
                    'old_balance' => $oldBalance,
                    'new_balance' => $newBalance,
                    'changed_by' => Auth::user() ? Auth::user()->id : 'guest',
                    'request_url' => Request::fullUrl(),
                    'request_data' => Request::all(),
                    'ip_address' => Request::ip(),
                    'user_agent' => Request::header('User-Agent'),
                ]);

                Event::log(EventTypes::BALANCE_CHANGED, $user->id, 'Баланс пользователя обновлен с ' . $oldBalance . '$ на ' . $newBalance . '$');
            }

        });

    }

    public function getKey(): int
    {
        return $this->id;
    }

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'decimal:8',
    ];

    public function usd_balance(): float
    {
        $data = Currency::getCurrencyPrice('BTC');

        if ($data) {
            return $this->balance * $data['price'];
        }

        return 0;
    }

    public function eur_balance(): float
    {
        $data = Currency::getCurrencyPrice('EUR');

        if ($data) {
            return self::usd_balance() / $data['price'];
        }

        return 0;
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendDepositCompletedEmail(Deposit $deposit): void
    {
        $this->notify(new DepositCompleted($deposit));
    }

    public function sendWithdrawalCompletedEmail(Withdrawal $withdrawal): void
    {
        $this->notify(new WithdrawalCompleted($withdrawal));
    }

    public function sumOfSuccessDeposits()
    {
        return Cache::remember('user_deposits_sum_' . $this->id, 60, function () {
            $res = $this->deposits()->whereIn('status', [
                    DepositStatuses::APPROVED->value,
                    DepositStatuses::COMPLETED->value
                ]
            )->sum('amount');

            // Parse the result to a float
            return is_null($res) ? 0.0 : (float) $res;
        });
    }

    public function sumOfSuccessWithdrawals()
    {
        return Cache::remember('user_withdrawals_sum_' . $this->id, 60, function () {
            $res = $this->withdrawals()->whereIn('status', [
                    WithdrawalStatuses::APPROVED->value,
                    WithdrawalStatuses::COMPLETED->value
                ]
            )->sum('amount');

            // Parse the result to a float
            return is_null($res) ? 0.0 : (float) $res;
        });
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->getRawRoles(),
            'permissions' => $this->getRawPermissions(),
        ];
    }

    public function getAvatarUrl(): string|null
    {
        return $this->avatar ? asset('storage/'.StorageTypes::AVATAR->value.'/' . $this->avatar) : null;
    }


    public function getRawRoles(): array
    {
        return Cache::remember('user_roles_' . $this->id, 60, function () {
            return $this->roles->map(static fn(Role $role) => $role->name)->toArray();
        });
    }

    public function getRawPermissions(): array
    {
        return Cache::remember('user_permissions_' . $this->id, 60, function () {
            return [
                ...$this->roles->map(
                    static fn(Role $role) => $role->permissions->map(
                        static fn(Permission $permission) => $permission->name
                    )->toArray()
                )->collapse()->toArray(),
                ...$this->permissions->map(static fn(Permission $permission) => $permission->name)->toArray()
            ];
        });
    }

    public function getDepositSum(): float
    {
        return $this->deposits->sum('amount');
    }

}
