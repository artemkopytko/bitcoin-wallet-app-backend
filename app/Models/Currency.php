<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DepositStatuses;
use App\Enums\CurrencyTypes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property integer $id
 * @property string $code
 * @property string $widget_code
 * @property string $name
 * @property CurrencyTypes $type
 * @property numeric $price
 * @property numeric $ask
 * @property numeric $bid
 * @property Carbon $created_at
 * @property Carbon $updated_at
*/

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'price',
        'ask',
        'bid',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'ask' => 'decimal:2',
        'bid' => 'decimal:2',
    ];

    // Function to get any currency price, ask and bid with caching
    static function getCurrencyPrice($currencyCode) {
        return Cache::remember(
            "currency_price_{$currencyCode}",
            self::getCacheDuration($currencyCode),
            function () use ($currencyCode) {
                $currency = Currency::where('code', $currencyCode)->first();
                if ($currency) {
                    return [
                        'price' => $currency->price,
                        'ask' => $currency->ask,
                        'bid' => $currency->bid,
                    ];
                }
                return null;
            }
        );
    }

    // Function to get cache duration based on currency type
    private static function getCacheDuration($currencyCode) {
        $currency = Currency::where('code', $currencyCode)->first();
        if ($currency) {
            if ($currency->type === CurrencyTypes::CRYPTO) {
                return now()->addMinutes(1);
            } elseif ($currency->type === CurrencyTypes::FIAT) {
                return now()->addMinutes(60);
            }
        }
        return now()->addMinutes(1); // Default duration if not found
    }
}
