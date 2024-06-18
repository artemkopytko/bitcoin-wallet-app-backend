<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrenciesAssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = fopen(database_path('seeders/data/currencies.csv'), 'r');

        while ($csvData = fgetcsv($csvFile)) {
            $asset = [
                'code' => $csvData[0],
                'name' => $csvData[1],
                'type' => $csvData[2],
                'widget_code' => $csvData[3],
            ];

            Currency::updateOrCreate(
                ['code' => $asset['code']],
                $asset
            );
        }

        fclose($csvFile);
    }
}
