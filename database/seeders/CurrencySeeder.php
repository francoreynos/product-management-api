<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'ARS', 'name' => 'Argentine Peso', 'symbol' => '$'],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => 'US$'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                [
                    'name' => $currency['name'],
                    'symbol' => $currency['symbol'],
                ]
            );
        }
    }
}
