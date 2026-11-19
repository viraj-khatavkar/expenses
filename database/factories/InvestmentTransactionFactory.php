<?php

namespace Database\Factories;

use App\Models\InvestmentCategory;
use App\Models\InvestmentTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/** @extends Factory<InvestmentTransaction> */
class InvestmentTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => InvestmentCategory::factory(),
            'date' => Date::today()->toDateString(),
            'period' => fn (array $attributes): string => substr($attributes['date'], 0, 7),
            'direction' => 'deposit',
            'amount_paise' => fake()->numberBetween(100, 500000),
        ];
    }
}
