<?php

namespace App\Console\Commands;

use App\Enums\CurrencyTypes;
use App\Models\Currency;
use App\Services\CurrencyPricesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateCryptoCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-crypto-currencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating crypto currencies');
        $pricesService = new CurrencyPricesService();

        $assets = Currency::where(function ($query) {
                $query->where('updated_at', '<', now()->subMinutes(1))
                    ->orWhereNull('updated_at');
            })
            ->where('type', CurrencyTypes::CRYPTO)
            ->orderBy('updated_at', 'asc')
            ->take(50)
            ->get();

        foreach ($assets as $asset) {
            $res = $pricesService->fetchPrice($asset);

            if (empty($res)) {
                continue;
            }

            $asset->update([
                'price' => $res['price'],
                'ask' => $res['price'] * 1.01,
                'bid' => $res['price'] * 0.99,
            ]);
        }
    }
}
