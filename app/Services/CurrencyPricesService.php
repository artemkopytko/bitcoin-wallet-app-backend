<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;

class CurrencyPricesService
{
    public function fetchPrice(Currency $asset): array
    {
        $response = Http::get('https://query2.finance.yahoo.com/v8/finance/chart/' . $asset->widget_code . '?interval=1d&range=1d');

        if (!$response->successful()) {
            return [];
        }

        $data = $response->json();

        if (!isset($data)) {
            return [];
        }

        return [
            'price' => $data['chart']['result'][0]['meta']['regularMarketPrice']
        ];
    }
}
