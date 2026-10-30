<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => 'US$',
                'exchange_rate' => 1.000000,
            ],
            [
                'code' => 'ARS',
                'name' => 'Argentine Peso',
                'symbol' => '$',
                'exchange_rate' => 1500.000000,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'exchange_rate' => 0.920000,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                [
                    'name' => $currency['name'],
                    'symbol' => $currency['symbol'],
                    'exchange_rate' => $currency['exchange_rate'],
                ]
            );
        }
    }
}