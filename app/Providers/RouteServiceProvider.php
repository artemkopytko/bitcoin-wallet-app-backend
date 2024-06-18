<?php

namespace App\Providers;

use App\Enums\Roles;
use App\Models\{AssetHolding, CreditPosition, Deposit, Event, Message, SpotOrder, User, Market, Withdrawal};
use Date;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\UnauthorizedException;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(1200)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        $this->bindModelWithUser('withdrawal', Withdrawal::class);
        $this->bindModelWithUser('deposit', Deposit::class);
        $this->bindModelWithUser('event', Event::class);
    }

    /**
     * Helper method to bind model with user role check.
     *
     * @param string $name
     * @param string $model
     */
    private function bindModelWithUser(string $name, string $model): void
    {
        Route::bind($name, static function ($value) use ($model) {
            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                throw new UnauthorizedException();
            }

            $query = $model::where('id', $value);

            if ($user->hasRole(Roles::USER)) {
                $query->where('user_id', $user->id);
            }

            return $query->firstOrFail();
        });
    }
}
